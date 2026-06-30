<?php

namespace AutoJax {


/**
 * @file password.php
 *
 * password management service -- two actions, one workflow:
 *
 *   read   -- "forgot password": look up the user by email or display name,
 *             issue a short-lived password_reset JWT, and email a fragment link
 *
 *   update -- "reset password": consume the reset JWT and set a new password
 *
 * both actions are unauthenticated (no session JWT required or expected):
 *   read   is by definition called when the user cannot log in
 *   update carries a password_reset JWT in $param, not a session JWT
 *
 * security properties:
 *   - read response is always identical regardless of whether the identifier was found
 *     (prevents user enumeration)
 *   - reset token is delivered in the URL fragment (#token), never the query string.
 *     the fragment is never sent to the server and does not appear in any server logs
 *   - the reset link is single-use. only a hash of the latest-issued token is stored, and a successful update
 *     clears it and stamps lastRevocation, so the link cannot be replayed and all active sessions end at once
 *   - only the most recently issued link is live. each issue overwrites the stored hash, so near-simultaneous
 *     requests cannot leave several usable links at once
 *   - generic error message for all token failures (no indication of which check failed)
 *
 * throttling:
 *   read   -- THROTTLE_PASSWORD_RESET_REQUEST per IP. per-user cooldown via passwordResetRequestedAt
 *   update -- THROTTLE_PASSWORD_RESET_ATTEMPT per IP
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

require_once __DIR__ . "/../util/util.php";
require_once __DIR__ . "/../util/db.php";
require_once __DIR__ . "/../util/security.php";
require_once __DIR__ . "/../util/sendEmail.php";
require_once __DIR__ . "/authThrottling.php";

/**
 * required entry point -- dispatches to handleRead() or handleUpdate() based on action
 *
 * @param object $autoJaxRequest {
 *     @property string $action  "read" or "update"
 * }
 * @return void
 */
function serviceRequest() {
    global $autoJaxRequest, $clientIpAddress;
    if (isset($autoJaxRequest->subService)) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_REQUEST);
        dieGracefully(__attack__, "Attack: password service does not use subService :: " . json_encode($_SERVER));
    }
    if ($autoJaxRequest->action === "read") {
        handleRead();
    }
    else {
        if ($autoJaxRequest->action === "update") {
            handleUpdate();
        }
        else {
            recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_REQUEST);
            dieGracefully(__attack__, "Attack: unsupported action for password service :: " . json_encode($_SERVER));
        }
    }
}

// -------------------------------------------------------------------------
// read -- forgot password: issue a reset link
// -------------------------------------------------------------------------

/**
 * handles the "forgot password" flow: look up the user, issue a reset JWT, send the link by email
 *
 * @return void
 */
function handleRead() {
    global $autoJaxConfig, $autoJaxRequest, $clientIpAddress;
    // ACCEPTED RESIDUAL -- do not "fix" without re-reading this. found-and-active identifiers do more work
    // here (mint token, stamp, decrypt, send email) than unknown ones, so response timing is a weak oracle
    // for whether an account exists. this is deliberately left open: it reveals only the existence bit, which
    // registration already discloses more sharply (the "account already exists" response on a duplicate). the
    // mitigations -- a symmetric SMTP transaction on the not-found path, or a flush-then-send with a transport
    // probe -- each cost more than the bit is worth on a low-traffic, per-request PHP host (needless SMTP
    // connections risk sender-reputation blacklisting. there is no long-lived process to amortize a probe).
    // closing this while leaving the sharper registration oracle open would be motion, not security.
    if ( ! isset($autoJaxRequest->param->identifier)) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_REQUEST);
        dieGracefully(__attack__, "Attack: password/read missing identifier :: " . json_encode($_SERVER));
    }
    if (isIpThrottled($clientIpAddress, THROTTLE_PASSWORD_RESET_REQUEST)) {
        writeLog("authEvent", "password/read throttled for ip: {$clientIpAddress}");
        ajaxExit(TOO_MANY_REQUESTS);
    }
    $identifier = normalizeWhitespace($autoJaxRequest->param->identifier); // email address or display name
    if ($identifier === "" || strlen($identifier) > 254) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_REQUEST);
        dieGracefully(__attack__, "Attack: invalid identifier in password/read :: " . json_encode($_SERVER));
    }
    // count every well-formed reset request toward the per-IP request cap, not only the malformed ones,
    // so passwordResetRequestThrottle.maxIpFails actually bounds reset-request volume from a single source
    recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_REQUEST);
    $identifierHash = lowercaseHash($identifier);
    try {
        $stmt = dbQuery(
            "password/read",
            "SELECT autoJaxUserId, email_enc, passwordResetRequestedAt FROM autoJaxUser
             WHERE (email_hash = ? OR displayName_hash = ?)
             AND accountStatus = 'active'",
            "ss",
            [ $identifierHash, $identifierHash ]
        );
        $stmt->bind_result($autoJaxId, $email_enc, $requestedAt);
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "password/read db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ( ! $found) {
        writeLog("authEvent", "password/read: identifier not found (possible enumeration) for ip: {$clientIpAddress}");
        ajaxExit(NO_CONTENT);
    }
    // per-user cooldown: passwordResetRequestedAt is stamped each time a reset link is issued
    // if a link was issued recently, skip sending another one silently
    if ($requestedAt !== null) {
        $secondsSinceRequest = time() - dbTimestampToEpoch($requestedAt);
        $cooldownInSeconds   = $autoJaxConfig->passwordReset->cooldownInMinutes * 60;
        if ($secondsSinceRequest < $cooldownInSeconds) {
            writeLog("authEvent", "password/read: cooldown active for uid: {$autoJaxId}");
            ajaxExit(NO_CONTENT);
        }
    }
    $durationInSeconds = $autoJaxConfig->passwordReset->ttlInSeconds;
    try {
        $token = createJwt($autoJaxId, "password_reset", null, null, $durationInSeconds);
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "password/read could not create JWT: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    // record this token as the one live reset link for the user, and stamp the request time for the cooldown.
    // we store only a hash of the token (hashToken), never the token itself, so a database leak exposes no
    // usable link. overwriting any previous hash supersedes earlier links: if several "forgot password"
    // requests land close together, only the most recently issued link matches on consume, so they cannot all
    // stay live at once. no clock alignment is needed here -- consume is an exact hash match, not an iat compare
    try {
        dbQuery(
            "password/read",
            "UPDATE autoJaxUser SET passwordResetTokenHash = ?, passwordResetRequestedAt = NOW() WHERE autoJaxUserId = ?",
            "si",
            [ hashToken($token), $autoJaxId ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "password/read could not store reset token hash: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    try {
        $emailAddress = decryptValueWithOverlap($email_enc); // binary blob from db, not b64
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "password/read could not decrypt email: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    // token goes in the URL fragment -- never the query string
    // the fragment is not sent to the server and does not appear in any logs
    // $autoJaxConfig->passwordResetUrl is derived from parentUrl in autojax.json
    $resetUrl     = $autoJaxConfig->passwordResetUrl . "#" . $token;
    $expiresInMin = (int)round($durationInSeconds / 60);
    $subject      = "Password reset request";
    $body         =
        "# Password reset requested\n\n" .
        "A password reset was requested for your account.\n\n" .
        "Click the link below to set a new password. " .
        "This link expires in **{$expiresInMin} minutes**.\n\n" .
        $resetUrl . "\n\n" .
        "If you did not request this, you can safely ignore this email. " .
        "Your password has not been changed.";
    captureTestToken("password_reset", $autoJaxId, $emailAddress, $token, $resetUrl);
    sendEmail($subject, $body, $emailAddress);
    writeLog("authEvent", "password/read: reset link sent for uid: {$autoJaxId}");
    ajaxExit(NO_CONTENT);
}

// -------------------------------------------------------------------------
// update -- reset password: consume the token and set a new password
// -------------------------------------------------------------------------

/**
 * handles the "reset password" flow: validate the reset JWT and commit a new password
 *
 * validation pipeline:
 *   1. signature valid and type === "password_reset"
 *   2. exp not passed (handled inside decodeJwtWithOverlap)
 *   3. hashToken(token) matches autoJaxUser.passwordResetTokenHash -- the one live, not-yet-used reset link
 *   4. new password passes isValidPassword()
 *   5. update: password_hash, passwordResetTokenHash = NULL, lastRevocation = NOW()
 *      consumes this link (clearing the hash blocks replay and supersedes any other link) and ends all sessions
 *
 * @return void
 */
function handleUpdate() {
    global $autoJaxConfig, $autoJaxRequest, $clientIpAddress;
    if ( ! isset($autoJaxRequest->param->token, $autoJaxRequest->param->password)) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT);
        dieGracefully(__attack__, "Attack: password/update missing token or password :: " . json_encode($_SERVER));
    }
    if (isIpThrottled($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT)) {
        writeLog("authEvent", "password/update throttled for ip: {$clientIpAddress}");
        ajaxExit(TOO_MANY_REQUESTS);
    }
    $password = normalizeWhitespace($autoJaxRequest->param->password, true);
    $token = $autoJaxRequest->param->token;
    // 1. verify signature and enforce type === "password_reset"
    try {
        $payload = decodeJwtWithOverlap($token, true, "password_reset");
    }
    catch (\Throwable $thr) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT);
        dieGracefully(
            __attack__,
            "Attack: invalid password reset token from ip: {$clientIpAddress} -- " .
            $thr->getMessage()
        );
    }
    // 2. expiry is checked inside decodeJwt. an expired link is a recoverable user-facing outcome, not an
    //    attack: return UNPROCESSABLE so the reset page tells the user to request a new link. 401 here would
    //    be wrong -- the client turns 401 into a "session expired" logout, but this page holds no session
    if ($payload === "expired") {
        writeLog("authEvent", "password/update: expired token for ip: {$clientIpAddress}");
        ajaxExit(UNPROCESSABLE, [ "reason" => "expired reset link" ]);
    }
    if ( ! is_object($payload)) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT);
        dieGracefully(__attack__, "Attack: password/update got non-object payload :: " . json_encode($_SERVER));
    }
    $autoJaxId = $payload->autoJaxId;
    // 3. fetch the stored reset-token hash for the single-use / supersession check
    try {
        $stmt = dbQuery(
            "password/update",
            "SELECT passwordResetTokenHash FROM autoJaxUser WHERE autoJaxUserId = ? AND accountStatus = 'active'",
            "i",
            [ $autoJaxId ]
        );
        $stmt->bind_result($storedHash);
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "password/update db failure fetching user: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ( ! $found) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT);
        dieGracefully(__attack__, "Attack: password/update uid not found or account not active for ip: {$clientIpAddress}");
    }
    // single-use + supersession: the presented token must hash-match the one live reset link stored for this
    // user. a null hash (already consumed, or never issued) or any mismatch (an older, superseded link, or a
    // replay of a link already used) both fail. this is also exactly what a retry after a dropped success
    // response looks like (the reset committed and cleared the hash, the 204 was lost, the client re-sent the
    // now-consumed token), so treat it as a recoverable "stale link" the reset page can explain rather than a
    // fatal attack. still record the attempt so token replay stays throttled. hash_equals keeps the compare
    // timing-flat, and short-circuiting on a null hash avoids passing null to it
    if ($storedHash === null || ! hash_equals($storedHash, hashToken($token))) {
        recordIpAddress($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT);
        writeLog("authEvent", "password/update: stale (used, superseded, or revoked) token for uid: {$autoJaxId}");
        ajaxExit(UNPROCESSABLE, [ "reason" => "invalid or already-used reset link" ]);
    }
    // 4. validate the new password
    if ( ! isValidPassword($password)) {
        dieGracefully(__attack__, "Attack: password/update password does not meet requirements for ip: {$clientIpAddress}");
    }
    // 5. hash and commit -- one atomic update consumes this link (clears the hash), supersedes any other
    //    outstanding link, and ends all active sessions (lastRevocation)
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    try {
        dbQuery(
            "password/update",
            "UPDATE autoJaxUser SET
                password_hash          = ?,
                passwordResetTokenHash = NULL,
                lastRevocation         = NOW()
            WHERE autoJaxUserId = ?",
            "si",
            [ $passwordHash, $autoJaxId ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "password/update db failure updating password: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    resetIpThrottle($clientIpAddress, THROTTLE_PASSWORD_RESET_ATTEMPT);
    writeLog("authEvent", "password/update: successful password reset for uid: {$autoJaxId}");
    ajaxExit(NO_CONTENT);
}


} // end namespace AutoJax

