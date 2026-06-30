<?php

namespace AutoJax;

/**
 * @file security.php
 *
 * encryption, decryption, hashing, jwt processing, and basic password validation
 *
 * encryptValue() / decryptValue() use AES-256-GCM with random IVs for sensitive data
 * lowercaseHash() and hashToken() give SHA-256 hashes (for database searches and token references) without
 * storing the original value
 * createJwt() / decodeJwtWithOverlap() for crypto-opaque auth tokens
 *
 * jwt architecture: minimal server-side state, one indexed read per authenticated request, no session table
 *
 * every jwt payload carries a "type" field that scopes the token to a single purpose:
 *   "session"        - normal authenticated session (regular users and admins alike),
 *                      revoked via autoJaxUser.lastRevocation. any session jwt issued at or before that
 *                      stamp is rejected
 *   "password_reset" - single-use reset token, matched against autoJaxUser.passwordResetTokenHash. issuing a
 *                      link stores the new token's hash and supersedes any earlier link, and consuming a link
 *                      clears the hash, so each link works once and only the most recently issued link is live
 *   "email_confirm"  - self-registration confirmation token. consuming it moves a "pending"
 *                      account to "active" (see confirmEmail.php)
 * admin authority is indicated by autoJaxUser.isAdmin = 1, not by a separate token type.
 * admin sessions are regular sessions that admin.php additionally gate-checks
 *
 * revocation needs almost no server-side state. a session is revoked by stamping autoJaxUser.lastRevocation,
 * and any token whose iat predates it is rejected, costing one column read on a row already fetched for
 * accountStatus. a password-reset link is single-use via autoJaxUser.passwordResetTokenHash rather than a
 * timestamp, so replay and supersession are exact hash matches, not clock comparisons
 *
 * credential rotation:
 *   decodeJwtWithOverlap() is the public entry point for jwt decoding. it wraps the internal
 *   decodeJwt() with retry logic to support in-flight tokens issued under recently rotated credentials.
 *
 *   decryptValueWithOverlap() is the public entry point for decrypting field values stored in the
 *   database. it wraps decryptValue() with the same retry logic so that values encrypted under a
 *   recently rotated key remain readable during the grace window.
 *
 *   the config object may carry up to two sets of old credentials, each with its own rotation timestamp:
 *     encryptionKeyOld + encKeyLastUpdate         -- previous encryption key and when it was replaced
 *     jwt->secretOld   + jwt->secretLastUpdate -- previous signing secret and when it was replaced
 *
 *   the grace window for each old credential is:
 *     rotatedAt + (jwt->durationInMinutes * 60) + ROTATION_GRACE_BUFFER_SECONDS
 *   this guarantees every token issued under the old credential gets its full lifetime, regardless
 *   of when during the previous period it was issued. ROTATION_GRACE_BUFFER_SECONDS covers clock
 *   skew and the instant of rotation itself.
 *
 *   decodeJwtWithOverlap() tries all credential combinations that are (a) distinct from the primary
 *   attempt and (b) still within their individual grace window. any success wins. all failures throw.
 *   retry combinations attempted when in-window:
 *     old key + old secret     (both rotated and both in-window)
 *     old key + current secret (only key rotated and in-window)
 *     current key + old secret (only secret rotated and in-window)
 *
 *   decodeJwt() and decryptValue() are internal. call sites should use decodeJwtWithOverlap()
 *   and decryptValueWithOverlap() respectively.
 *
 * config dependencies (all read from global $autoJaxConfig, validated at startup by the package bootstrap):
 *   $autoJaxConfig->encryptionKey            -- current AES-256 key, hex 64 chars
 *   $autoJaxConfig->encryptionKeyOld         -- (optional) previous key. must be paired with encKeyLastUpdate
 *   $autoJaxConfig->encKeyLastUpdate         -- (optional) unix timestamp of last key rotation
 *   $autoJaxConfig->jwt->secret              -- current HMAC-SHA256 signing secret, hex 64 chars
 *   $autoJaxConfig->jwt->secretOld           -- (optional) previous secret. must be paired with secretLastUpdate
 *   $autoJaxConfig->jwt->secretLastUpdate -- (optional) unix timestamp of last secret rotation
 *   $autoJaxConfig->jwt->durationInMinutes   -- token lifetime in minutes, integer, clamped to the range 5-480 at
 *                                               config assembly (values outside are silently moved to the nearest end).
 *                                               behaves as an idle timeout since each response refreshes the token.
 *                                               keep it at 20 or less. longer sessions weaken the idle-timeout guard
 *   $autoJaxConfig->jwt->absoluteMaxInMinutes -- hard cap on total session age, integer minutes, clamped 5-480 at
 *                                               config assembly and never below durationInMinutes. a refresh carries
 *                                               sessionStart forward so this bounds how long any token can be replayed
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

// config already loaded by host wrapper

// seconds added to the rotation grace window to cover clock skew and the instant of rotation
const ROTATION_GRACE_BUFFER_SECONDS = 15;

// earliest unix timestamp accepted for encKeyLastUpdate and secretLastUpdate (2025-01-01 00:00:00 UTC)
const ROTATION_TIMESTAMP_FLOOR = 1735689600;

// how far in the future a token's iat may be (clock-skew tolerance). beyond this the token is rejected
const JWT_IAT_FUTURE_SKEW_SECONDS = 60;

/**
 * the credential-rotation grace-window base, in seconds: the longest lifetime any token could have been
 * issued with under a now-rotated credential. old credentials must stay honored at least this long after
 * rotation so nothing minted under them is rejected before its own expiry. taking the max across the
 * session, password-reset, and email-confirm lifetimes matters because reset and confirm tokens outlive a
 * session, so sizing the window to the session alone would reject a still-valid reset or confirm link
 * issued just before a rotation. tolerant of a minimal $autoJaxConfig (the CLI tools build one without the
 * token-ttl settings), falling back to whatever lifetimes are present.
 *
 * @return int seconds
 */
function rotationWindowBaseSeconds() {
    global $autoJaxConfig;
    $session = isset($autoJaxConfig->jwt->durationInMinutes) ? (int)$autoJaxConfig->jwt->durationInMinutes * 60 : 0;
    $reset   = isset($autoJaxConfig->passwordReset->ttlInSeconds) ? (int)$autoJaxConfig->passwordReset->ttlInSeconds : 0;
    $confirm = isset($autoJaxConfig->emailConfirm->ttlInMinutes) ? (int)$autoJaxConfig->emailConfirm->ttlInMinutes * 60 : 10 * 60;
    return max($session, $reset, $confirm);
}

/**
 * encrypt a plaintext (character or binary) string using AES-256-GCM with a random IV and tag
 *
 * @param string      $plaintext    data to encrypt
 * @param bool        $b64          if true, returns base64url-encoded string. if false, returns binary blob
 * @param string|null $keyOverride  encryption key override (hex, 64 chars). defaults to $autoJaxConfig->encryptionKey
 * @return string binary blob or base64url-encoded string (IV + ciphertext + tag)
 */
function encryptValue($plaintext, $b64 = false, $keyOverride = null) {
    global $autoJaxConfig;
    $key = $keyOverride ?? $autoJaxConfig->encryptionKey;
    if ( ! preg_match('/^[a-f0-9]{64}$/i', $key)) {
        throw new \Exception("invalid encryption key");
    }
    $key = hex2bin($key);
    if ($key === false || strlen($key) !== 32) {
        throw new \Exception("hex2bin failed to convert encryption key");
    }
    $iv  = random_bytes(12);
    $tag = null; // passed by ref, replaced with real value by openssl_encrypt
    try {
        $ciphertext = openssl_encrypt($plaintext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
    catch (\Throwable $thr) {
        throw new \Exception(
            "openssl_encrypt threw: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ($ciphertext === false) {
        throw new \Exception("openssl_encrypt failed");
    }
    if (strlen($tag) !== 16) {
        throw new \Exception("openssl_encrypt returned unexpected tag length");
    }
    $bin = $iv . $ciphertext . $tag;
    return $b64 ? base64url_encode($bin) : $bin;
}

/**
 * decrypt something created by encryptValue()
 *
 * internal -- call sites should use decryptValueWithOverlap() to benefit from the key rotation
 * grace window. use decryptValue() directly only when managing the key explicitly, as in the
 * re-encryption utility.
 *
 * @param string      $blob         binary or base64url of IV + ciphertext + tag
 * @param bool        $b64          if true, treats $blob as base64url-encoded input
 * @param string|null $keyOverride  encryption key override (hex, 64 chars). defaults to $autoJaxConfig->encryptionKey
 * @param bool        $throw        if false, return false on decryption failure instead of throwing.
 *                                  used by decryptValueWithOverlap to avoid PHP 8.5 + Xdebug 3.5
 *                                  coverage-mode catch-block poisoning on repeated calls
 * @return string|false decrypted plaintext, or false when $throw is false and decryption fails
 */
function decryptValue($blob, $b64 = false, $keyOverride = null, $throw = true) {
    global $autoJaxConfig;
    if ( ! is_string($blob)) {
        if ( ! $throw) {
            return false;
        }
        throw new \Exception("blob to decrypt must be a (character or binary) string");
    }
    if ($b64) {
        $blob = base64url_decode($blob);
    }
    if (strlen($blob) < 28) { // 12 (IV) + 16 (tag) minimum
        if ( ! $throw) {
            return false;
        }
        throw new \Exception("blob too small to be valid");
    }
    $key = $keyOverride ?? $autoJaxConfig->encryptionKey;
    if ( ! preg_match('/^[a-f0-9]{64}$/i', $key)) {
        if ( ! $throw) {
            return false;
        }
        throw new \Exception("invalid encryption key");
    }
    $key = hex2bin($key);
    if ($key === false || strlen($key) !== 32) {
        if ( ! $throw) {
            return false;
        }
        throw new \Exception("hex2bin failed to convert encryption key");
    }
    $iv         = substr($blob, 0, 12);
    $tag        = substr($blob, -16) . ""; // force a true copy -- openssl_decrypt takes $tag by ref
    $ciphertext = substr($blob, 12, -16);
    try {
        $plaintext = openssl_decrypt($ciphertext, "aes-256-gcm", $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
    catch (\Throwable $thr) {
        if ( ! $throw) {
            return false;
        }
        throw new \Exception(
            "openssl_decrypt threw: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ($plaintext === false) {
        if ( ! $throw) {
            return false;
        }
        throw new \Exception("openssl_decrypt failed");
    }
    return $plaintext;
}

/**
 * attempt a single decryption with the given key. return the plaintext string on success, or
 * false on any failure. calls decryptValue with $throw=false to avoid any exception being thrown,
 * which bypasses a PHP 8.5 + Xdebug 3.5 + coverage-mode bug where catch blocks are silently
 * skipped after being visited once by the observer API.
 *
 * @param string $blob  binary or base64url blob
 * @param bool   $b64   passed through to decryptValue
 * @param string $key   candidate key (hex, 64 chars)
 * @return string|false plaintext on success, false on any failure
 */
function tryDecryptValue($blob, $b64, $key) {
    return decryptValue($blob, $b64, $key, false);
}

/**
 * decrypt something created by encryptValue(), with automatic retry across a recently changed key
 *
 * public entry point for decrypting stored field values. tries the current key first (most likely
 * case), then retries with the old key if it is still within its grace window. any success wins.
 * all failures throw.
 *
 * this is the correct function to call when reading encrypted columns from the database during
 * normal application operation. it ensures values written under the previous key remain readable
 * while the re-encryption utility catches up.
 *
 * grace window: encKeyLastUpdate + rotationWindowBaseSeconds() + ROTATION_GRACE_BUFFER_SECONDS, where
 * rotationWindowBaseSeconds() is the longest of the session, password-reset, and email-confirm lifetimes
 * (same formula as decodeJwtWithOverlap, kept consistent so both windows expire together)
 *
 * $keyOverride replaces the "current" key. it does not suppress the old-key retry -- the old key
 * is a rotation concern independent of any per-call override.
 *
 * @param string      $blob         binary or base64url of IV + ciphertext + tag
 * @param bool        $b64          if true, treats $blob as base64url-encoded input
 * @param string|null $keyOverride  encryption key override (hex, 64 chars). replaces current key
 * @return string decrypted plaintext
 */
function decryptValueWithOverlap($blob, $b64 = false, $keyOverride = null) {
    global $autoJaxConfig;
    $now        = time();
    $windowBase = rotationWindowBaseSeconds();
    $oldKeyInWindow =
        isset($autoJaxConfig->encryptionKeyOld) &&
        $now < $autoJaxConfig->encKeyLastUpdate + $windowBase + ROTATION_GRACE_BUFFER_SECONDS;
    $oldKey = $oldKeyInWindow ? $autoJaxConfig->encryptionKeyOld : null;
    // current key always first
    // old key appended only when it is actually in-window
    // tryDecryptValue returns false on failure (no exceptions thrown) to avoid a PHP 8.5 +
    // Xdebug 3.5 + coverage-mode bug that silently breaks catch blocks after first use
    $candidates = [
        $keyOverride ?? $autoJaxConfig->encryptionKey,
    ];
    if ($oldKey !== null) {
        $candidates[] = $oldKey;
    }
    foreach ($candidates as $candidateKey) {
        $result = tryDecryptValue($blob, $b64, $candidateKey);
        if ($result !== false) {
            return $result;
        }
    }
    throw new \Exception("decryption failed with all candidate keys");
}

/**
 * force input to all lowercase and hash the result
 * suitable for making case-insensitive unique identifiers searchable without storing plaintext
 *
 * @param string $str  any string, typically an email address or display name
 * @return string SHA-256 hash of lowercased, trimmed input
 */
function lowercaseHash($str) {
    return hash("sha256", mb_strtolower(trim($str)));
}

/**
 * SHA-256 hash of a token, used to store a reference to a token without storing the token itself.
 * unlike lowercaseHash(), the input is hashed verbatim. a token is case-sensitive and carries no surrounding
 * whitespace, so it must not be lowercased or trimmed. used for the single-use password-reset token: the
 * issued token's hash is stored on the user row, and a presented token is matched against it with hash_equals.
 *
 * @param string $token  the token string (a base64url JWT)
 * @return string  lowercase hex SHA-256 (64 chars)
 */
function hashToken($token) {
    return hash("sha256", $token);
}

/**
 * create a signed, encrypted jwt auth token for a given user ID and token type
 *
 * @param mixed       $autoJaxId         user ID to include in the token. must be truthy (non-zero, non-empty)
 * @param string      $type              token purpose: "session" or "password_reset"
 * @param string|null $keyOverride       encryption key override (hex, 64 chars)
 * @param string|null $secretOverride    signing secret override
 * @param int|null    $durationInSeconds token lifetime. defaults to $autoJaxConfig->jwt->durationInMinutes * 60
 * @param int|null    $sessionStart      unix time the session began. copied forward unchanged on every refresh so
 *                                        the absolute-lifetime cap cannot be extended by keep-alives. defaults to
 *                                        now, which is correct for a brand-new session (login)
 * @param string|null $sessionId         session tokens only: a stable per-session id. generated fresh here when
 *                                        null (a brand-new login), and passed in (carried forward unchanged) on
 *                                        every refresh, exactly like sessionStart. single-session mode matches its
 *                                        hash against the user row, so it must not change as the token rotates.
 *                                        ignored for non-session token types
 * @return string jwt in string form
 */
function createJwt($autoJaxId, $type = "session", $keyOverride = null, $secretOverride = null, $durationInSeconds = null, $sessionStart = null, $sessionId = null) {
    global $autoJaxConfig;
    if ( ! $autoJaxId) {
        throw new \Exception("invalid autoJaxId -- must be truthy");
    }
    $validTypes = ["session", "password_reset", "email_confirm", "cross_page"];
    if ( ! in_array($type, $validTypes, true)) {
        throw new \Exception("invalid jwt type: " . $type);
    }
    $header = base64url_encode(json_encode([
        "alg" => "HS256",
        "typ" => "jwt"
    ]));
    $time     = time();
    $duration = $durationInSeconds ?? ($autoJaxConfig->jwt->durationInMinutes * 60);
    $claims   = [
        "autoJaxId"    => $autoJaxId,
        "type"         => $type,
        "iat"          => $time,
        "exp"          => $time + $duration,
        "sessionStart" => $sessionStart ?? $time
    ];
    // session tokens always carry a sessionId so the payload shape is constant. generate one when none is
    // passed (login), reuse the one carried forward otherwise (refresh). single-session enforcement keys on it.
    // other token types (reset, email_confirm, cross_page) have no session identity, so they omit it
    if ($type === "session") {
        $claims["sessionId"] = $sessionId ?? bin2hex(random_bytes(16));
    }
    $payload = json_encode($claims);
    $encryptedData = encryptValue($payload, true, $keyOverride ?? $autoJaxConfig->encryptionKey); // can throw
    $dataToSign    = "$header.$encryptedData";
    $signature     = base64url_encode(hash_hmac("sha256", $dataToSign, $secretOverride ?? $autoJaxConfig->jwt->secret, true));
    return "$header.$encryptedData.$signature";
}

/**
 * validate and decode a jwt, returning the uid or the entire payload
 *
 * internal -- call sites should use decodeJwtWithOverlap() instead
 *
 * validates signature, expiry, and -- when $expectedType is given -- token type
 * type enforcement is a security check: passing a reset token to a session endpoint
 * (or vice versa) throws rather than silently succeeding
 *
 * @param string      $jwt            jwt string to process
 * @param bool        $fullDecode     if true, returns full payload object. if false, returns just the uid
 * @param string|null $expectedType   if set, throws if payload type does not match ("session" or "password_reset")
 * @param string|null $keyOverride    encryption key to use in place of $autoJaxConfig->encryptionKey
 * @param string|null $secretOverride signing secret to use in place of $autoJaxConfig->jwt->secret
 * @param bool        $throw          if false, return null on any hard failure instead of throwing.
 *                                    used by decodeJwtWithOverlap to avoid PHP 8.5 + Xdebug 3.5
 *                                    coverage-mode catch-block poisoning on repeated calls
 * @return int|string|\stdClass|null
 *     - string "expired": jwt is valid but past its expiry
 *     - int: user ID when $fullDecode is false
 *     - stdClass: full payload when $fullDecode is true
 *     - null: any hard failure when $throw is false
 */
function decodeJwt($jwt, $fullDecode = false, $expectedType = null, $keyOverride = null, $secretOverride = null, $throw = true) {
    global $autoJaxConfig;
    $parts = explode(".", $jwt);
    if (count($parts) !== 3) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("malformed jwt");
    }
    list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
    try {
        $header = jsonDecode(base64url_decode($headerEncoded));
    }
    catch (\Throwable $thr) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with invalid header");
    }
    if ( ! is_object($header)) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("required jwt header invalid");
    }
    if ( ! isset($header->alg) || $header->alg !== "HS256") {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("expected jwt algorithm to be declared as HS256");
    }
    if ( ! isset($header->typ) || $header->typ !== "jwt") {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("expected jwt type to be declared as jwt");
    }
    $expectedSig = base64url_encode(
        hash_hmac("sha256", "$headerEncoded.$payloadEncoded", $secretOverride ?? $autoJaxConfig->jwt->secret, true)
    );
    if ( ! hash_equals($expectedSig, $signatureEncoded)) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("invalid jwt signature");
    }
    $decryptedJson = decryptValue($payloadEncoded, true, $keyOverride, $throw);
    if ($decryptedJson === false) {
        return null; // $throw is false and decryption failed
    }
    try {
        $data = jsonDecode($decryptedJson);
    }
    catch (\Throwable $thr) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with invalid jwt payload");
    }
    if ( ! is_object($data)) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with invalid jwt payload");
    }
    if ( ! isset($data->autoJaxId) || ! $data->autoJaxId) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with missing or invalid uid");
    }
    if ( ! isset($data->type) || ! is_string($data->type)) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with missing or invalid type");
    }
    if ($expectedType !== null && $data->type !== $expectedType) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("jwt type mismatch: expected " . $expectedType . ", got " . $data->type);
    }
    if ( ! isset($data->iat) || ! is_int($data->iat) || $data->iat < 0) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with missing or invalid iat");
    }
    if ($data->iat > time() + JWT_IAT_FUTURE_SKEW_SECONDS) {
        // a signed token cannot have a forged iat, but reject an implausibly future iat (misissue or bad clock)
        // so it can never sit "before" a lastRevocation stamp and slip past the revocation check
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with iat too far in the future");
    }
    if ( ! isset($data->exp) || ! is_int($data->exp) || $data->exp < $data->iat) {
        if ( ! $throw) {
            return null;
        }
        throw new \Exception("decodeJwt called with missing or invalid exp");
    }
    if (time() > $data->exp) {
        return "expired";
    }
    return $fullDecode ? $data : $data->autoJaxId;
}

/**
 * attempt to decode a jwt with a single key/secret pair. return the decoded result on success,
 * or null on any failure. calls decodeJwt with $throw=false so no exception is ever thrown,
 * which bypasses a PHP 8.5 + Xdebug 3.5 + coverage-mode bug where catch blocks are silently
 * skipped after being visited once by the observer API.
 *
 * @param string      $jwt           jwt string to process
 * @param bool        $fullDecode    passed through to decodeJwt
 * @param string|null $expectedType  passed through to decodeJwt
 * @param string      $key           candidate encryption key (hex, 64 chars)
 * @param string      $secret        candidate signing secret
 * @return int|string|\stdClass|null  decoded result on success, null on any failure
 */
function tryDecodeJwt($jwt, $fullDecode, $expectedType, $key, $secret) {
    return decodeJwt($jwt, $fullDecode, $expectedType, $key, $secret, false);
}

/**
 * decode a jwt with automatic retry across recently rotated credentials
 *
 * public entry point for jwt decoding. tries the current credentials first, then retries with
 * old credentials for any combination that (a) differs from the primary attempt and (b) is still
 * within its grace window. any success wins. all failures throw.
 *
 * grace window for a given old credential:
 *   last update + rotationWindowBaseSeconds() + ROTATION_GRACE_BUFFER_SECONDS
 * where rotationWindowBaseSeconds() is the longest of the session, password-reset, and email-confirm
 * lifetimes. this guarantees every token issued under the old credential gets its full lifetime
 * regardless of when during the previous period it was issued.
 *
 * $keyOverride and $secretOverride replace the "current" credential in all combinations. they do
 * not suppress old-credential retries -- the old credentials are a rotation concern independent of
 * any per-call override.
 *
 * @param string      $jwt            jwt string to process
 * @param bool        $fullDecode     if true, returns full payload object. if false, returns just the uid
 * @param string|null $expectedType   if set, throws if payload type does not match ("session" or "password_reset")
 * @param string|null $keyOverride    encryption key override (hex, 64 chars). replaces current key in all candidates
 * @param string|null $secretOverride signing secret override. replaces current secret in all candidates
 * @return int|string|\stdClass
 *     - string "expired": jwt is valid but past its expiry
 *     - int: user ID when $fullDecode is false
 *     - stdClass: full payload when $fullDecode is true
 */
function decodeJwtWithOverlap($jwt, $fullDecode = false, $expectedType = null, $keyOverride = null, $secretOverride = null) {
    global $autoJaxConfig;
    $now        = time();
    $windowBase = rotationWindowBaseSeconds();
    // resolve each old credential to its actual value only if still within its grace window, else null
    // a null value means the old credential has expired and that candidate combination will be skipped
    $oldKeyInWindow =
        isset($autoJaxConfig->encryptionKeyOld) &&
        $now < $autoJaxConfig->encKeyLastUpdate + $windowBase + ROTATION_GRACE_BUFFER_SECONDS;
    $oldKey = $oldKeyInWindow ? $autoJaxConfig->encryptionKeyOld : null;
    $oldSecretInWindow =
        isset($autoJaxConfig->jwt->secretOld) &&
        $now < $autoJaxConfig->jwt->secretLastUpdate + $windowBase + ROTATION_GRACE_BUFFER_SECONDS;
    $oldSecret = $oldSecretInWindow ? $autoJaxConfig->jwt->secretOld : null;
    // honour any per-call overrides for the "current" slot
    $currentKey    = $keyOverride    ?? $autoJaxConfig->encryptionKey;
    $currentSecret = $secretOverride ?? $autoJaxConfig->jwt->secret;
    // build candidate pairs: current always first (vastly most likely), then old combinations only
    // when the relevant old credential is actually in-window (non-null)
    // skipping null candidates here avoids passing a null key or secret into decodeJwt() where the failure would be opaque
    // tryDecodeJwt returns null on failure (no exceptions thrown) to avoid a PHP 8.5 +
    // Xdebug 3.5 + coverage-mode bug that silently breaks catch blocks after first use
    $candidates = [
        [ $currentKey, $currentSecret ], // both current -- vastly most likely case
    ];
    if ($oldKey !== null && $oldSecret !== null) {
        $candidates[] = [ $oldKey, $oldSecret ]; // both rotated, token issued before either rotation
    }
    if ($oldKey !== null) {
        $candidates[] = [ $oldKey, $currentSecret ]; // key rotated, token issued before key rotation
    }
    if ($oldSecret !== null) {
        $candidates[] = [ $currentKey, $oldSecret ]; // secret rotated, token issued before secret rotation
    }
    foreach ($candidates as $candidate) {
        $result = tryDecodeJwt($jwt, $fullDecode, $expectedType, $candidate[0], $candidate[1]);
        if ($result !== null) {
            return $result;
        }
    }
    // all legitimate combinations exhausted with no success -- jwt is invalid (attack likely)
    throw new \Exception("jwt could not be decoded");
}

/**
 * validate that a password meets complexity requirements:
 *   - minLength to 72 characters. the upper bound is fixed at 72 (the number of bytes bcrypt actually
 *     hashes. bytes past it are ignored). the lower bound is config->password->minLength, clamped to
 *     12-72 and defaulting to 12 when unset, so it mirrors the client check in util.js exactly
 *   - printable ASCII only (0x21-0x7E) excluding backslash. no spaces, tabs, control characters, or non-ASCII
 *   - at least one lowercase letter
 *   - at least one uppercase letter
 *   - at least one digit
 *   - at least one symbol (any allowed printable ASCII non-alphanumeric character)
 *
 * the symbol class covers these characters:
 * ```
 * ~ ! @ # $ % ^ & * ( ) - _ = + [ { ] } | ; : ' " , < . > / ? `
 * ```
 * backslash (0x5C) is deliberately not an allowed character. it reads as an escape to many users and
 * tools even though it is stored and hashed literally, and permitting it invites quoting and escaping
 * mistakes in any code that later handles the password. space (0x20) is likewise excluded.
 * the first pattern therefore allows 0x21-0x7E except 0x5C, so both space and backslash are rejected.
 *
 * @param mixed $pwd  candidate password
 * @return bool       true if all rules pass, false otherwise
 */
function isValidPassword($pwd) {
    global $autoJaxConfig;
    $min = 12;
    if (isset($autoJaxConfig->password->minLength)) {
        $min = max(12, min(72, (int)$autoJaxConfig->password->minLength));
    }
    return is_string($pwd) &&
        preg_match('/^[\x21-\x5B\x5D-\x7E]{' . $min . ',72}$/', $pwd) &&
        preg_match('/[a-z]/', $pwd) &&
        preg_match('/[A-Z]/', $pwd) &&
        preg_match('/[0-9]/', $pwd) &&
        preg_match('/[~!@#$%^&*()\-_=+\[{\]};:\'",.\/<?>`|]/', $pwd);
}

/**
 * validate a display name. the allowed characters are exactly the password character set (printable ASCII
 * 0x21-0x7E, excluding backslash) with "@" additionally removed, length 1 to 254 characters.
 *
 * excluding "@" is the security-relevant part: every email address contains "@" and no display name may, so
 * the two namespaces stay disjoint and the identifier lookup (email_hash OR displayName_hash) can never match
 * one user by email and another by display name. space (0x20) is already outside the set. unicode is excluded
 * for the same reasons it is in a password -- cross-device normalization (NFC vs NFD differing by byte),
 * bcrypt-style byte limits, and reliable re-entry, the last especially on mobile -- and it additionally
 * prevents diacritic-only duplicates such as "Charlie" versus "Charlié" (the accented form simply cannot be
 * registered). there is no complexity requirement: this constrains the character set and length only.
 *
 * @param mixed $name  candidate display name
 * @return bool        true if the display name is allowed
 */
function isValidDisplayName($name) {
    return is_string($name) &&
        (bool)preg_match('/^[\x21-\x3F\x41-\x5B\x5D-\x7E]{1,254}$/', $name);
}

/**
 * encode and insert a new account, returning its autoJaxUserId.
 *
 * shared by self-registration (registerNewUser.php) and the autoJaxAdmin CLI so the stored encoding can
 * never diverge between the two entry points: the display name and email are hashed on their lowercased
 * value for case-insensitive lookup, encrypted as-typed so the ciphertext preserves the original casing,
 * and the password is stored only as a bcrypt hash. the caller must have already validated displayName,
 * email, and password (e.g. with isValidDisplayName and isValidPassword) before calling this.
 *
 * @param string $displayName  normalized, validated display name
 * @param string $email        normalized, validated email
 * @param string $password     validated plaintext password (stored only as a bcrypt hash)
 * @param string $status       accountStatus to set, e.g. "pending" or "active"
 * @param string $changedBy    statusChangedBy audit label, e.g. "self-registration" or "autoJaxAdmin"
 * @param bool   $isAdmin       whether the new account holds admin access
 * @return int|null  the new autoJaxUserId, or null when a unique-key collision means the account already exists
 * @throws \Throwable on any database error other than a duplicate-key (1062) collision
 */
function createUser($displayName, $email, $password, $status, $changedBy, $isAdmin = false) {
    $displayName_hash = lowercaseHash($displayName);
    $displayName_enc  = encryptValue($displayName);
    $email_hash       = lowercaseHash($email);
    $email_enc        = encryptValue($email);
    $password_hash    = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO autoJaxUser
                (displayName_hash, displayName_enc, email_hash, email_enc, password_hash,
                 accountStatus, statusChangedAt, statusChangedBy, isAdmin)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = dbQuery(
        "createUser",
        $sql,
        "sssssssi",
        [ $displayName_hash, $displayName_enc, $email_hash, $email_enc, $password_hash,
          $status, $changedBy, $isAdmin ? 1 : 0 ]
    );
    if ($stmt->errno === 1062) {
        return null;
    }
    return dbLastInsertId();
}
