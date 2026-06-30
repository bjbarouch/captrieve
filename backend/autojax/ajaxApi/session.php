<?php

namespace AutoJax {


/**
 * @file session.php
 *
 * the session lifecycle as one CRUD resource, dispatched on the action:
 *   create -- login.  begin a fresh session for the supplied credentials and issue a session jwt. public
 *             (auth cannot be required to log in). a jwt arriving with a login is ignored
 *   read   -- cross-page exchange.  mint a fresh access jwt from the httpOnly cross-page cookie, so a real
 *             navigation (a separate document, not an in-SPA route) can recover the memory-only session.
 *             public (the credential is the cookie, not a jwt). only meaningful when crossPageSession is on
 *   update -- keep-alive.  refresh the jwt of an already-authenticated session, no side effects. authenticated
 *   delete -- logout.  revoke the session server-side by stamping lastRevocation, so every session jwt for
 *             this user (iat before now) fails the revocation check in ajaxPort on its next use. authenticated.
 *             intentionally issues NO refreshed token (NO_CONTENT carries none), so a logout never hands back a
 *             still-valid session, and a keep-alive racing the logout loses -- its older token is rejected once
 *             this commits
 *
 * IP throttling is handled centrally in ajaxPort.php before this service is reached. this file handles only
 * login-name throttling on the create path, which requires knowledge of the login payload.
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
require_once __DIR__ . "/authThrottling.php";

// a fixed cost-10 bcrypt hash used only to equalize timing on the no-such-account path of login. its cost
// matches what password_hash(..., PASSWORD_BCRYPT) produces by default, so a dummy verify costs the same as a
// real verify regardless of the supplied password, closing the account-existence timing oracle
const AUTOJAX_DUMMY_PASSWORD_HASH = '$2y$10$//69FrmqGZRH.K5JcvN9hO1Rz3HoVbNpMiosBDbPSeleJoY5gUgJ.';

// the cross-page (multi-page) session cookie name. only present when an app opts in with
// crossPageSession in autojax.json. see sessionRead() for the exchange and the design in
// docs/routing-and-multipage-design.md section 4
const AUTOJAX_CROSS_PAGE_COOKIE = "autojaxCrossPage";

// the cross-page token rotates on every exchange. the token it just replaced stays valid for this grace
// window, so a tab that rotates does not invalidate a sibling tab still holding the token it read a moment
// earlier. it is short because its only job is to absorb the multi-tab race, not to extend a token's life
const AUTOJAX_CROSS_PAGE_GRACE_SECONDS = 120;

/**
 * required entry point. dispatches on the CRUD action to the matching session operation.
 *
 * ajaxPort has already vetted that the action is part of this service's api (create/update/delete are non-null
 * in autojax-services.php) and enforced the per-action auth requirement (create public, update/delete
 * authenticated), so an unsupported action never reaches here. the default arm is a defensive backstop.
 *
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function serviceRequest() {
    global $autoJaxRequest;
    switch ($autoJaxRequest->action ?? null) {
        case "create":
            sessionCreate();
            break;
        case "read":
            sessionRead();
            break;
        case "update":
            sessionUpdate();
            break;
        case "delete":
            sessionDelete();
            break;
        default:
            dieGracefully(__attack__, "Invalid session action :: " . json_encode($_SERVER));
    }
}

/**
 * create == login.
 *
 * a login request may arrive with or without a jwt -- conceptually none, since not yet logged in, but possibly
 * one left over from a previous session. if given, it is ignored. on success, issue a fresh jwt for the user
 * identified by the supplied credentials to start a session. for unmatched credentials, responds with
 * __unauthenticated__.
 *
 * @param object $autoJaxRequest {
 *     @property string $action     Must be "create"
 *     @property string $subService Must be null or empty
 *     @property object $param {
 *         @property string $loginName  The user's login email address or display name
 *         @property string $password   The user's plaintext password
 *     }
 * }
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function sessionCreate() {
    global $autoJaxConfig, $autoJaxRequest;
    if (
        ! isset($autoJaxRequest->param, $autoJaxRequest->param->loginName, $autoJaxRequest->param->password) ||
        isset($autoJaxRequest->subService)
    ) {
        writeLog("authEvent", "misconfigured login attempt :: " . json_encode($_SERVER));
        dieGracefully(__attack__, "misconfigured login attempt :: " . json_encode($_SERVER));
    }
    $param         = $autoJaxRequest->param;
    $loginNameHash = lowercaseHash(normalizeWhitespace($param->loginName));
    $password      = normalizeWhitespace($param->password, true);
    if (isLoginNameHashThrottled($loginNameHash)) {
        writeLog("authEvent", "login request throttled for loginNameHash: {$loginNameHash}");
        ajaxExit(TOO_MANY_REQUESTS, [ "reason" => "too many attempts" ]);
    }
    // opportunistic lazy cleanup (shared hosts have no cron): drop expired unconfirmed accounts, so a
    // login against an expired-pending account reads as "not found", and sweep stale throttle rows so
    // login_name counters for probed nonexistent names cannot accumulate unbounded
    purgeExpiredPendingAccounts();
    purgeExpiredThrottleRows();
    try {
        $stmt = dbQuery(
            "login",
            "SELECT autoJaxUserId, displayName_enc, email_enc, password_hash, accountStatus, isAdmin FROM autoJaxUser WHERE email_hash = ? OR displayName_hash = ?",
            "ss",
            [ $loginNameHash, $loginNameHash ]
        );
    }
    catch (\Throwable $thr) {
        writeLog("authEvent", "attempted login died on db failure :: " . json_encode($_SERVER));
        dieGracefully(
            SERVER_ERROR,
            "Could not log user in: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    $stmt->bind_result($autoJaxId, $displayName_enc, $email_enc, $password_hash, $accountStatus, $isAdmin);
    $found = $stmt->fetch();
    $stmt->close();
    // run one bcrypt operation on every attempt so response timing does not reveal whether the account
    // exists. for a missing account we hash the supplied password and discard the result, purely to spend
    // the same time a real password_verify would have taken
    if ($found) {
        $passwordVerified = password_verify($password, $password_hash);
    }
    else {
        // no such account: still spend one bcrypt verify against a fixed, cost-matched dummy hash so response
        // timing does not reveal whether the account exists. verifying a precomputed hash (rather than hashing
        // the supplied password) keeps the cost independent of the input and equal to a real verify's
        password_verify($password, AUTOJAX_DUMMY_PASSWORD_HASH);
        $passwordVerified = false;
    }
    if ($passwordVerified) {
        // check account status after verifying credentials, not before:
        // varying the response prior to password_verify would leak whether the account exists
        if ($accountStatus === "pending") {
            // correct password, but the email was never confirmed -- revealed only post-verify, so
            // it is not an enumeration leak. tells the legitimate owner to check their inbox
            writeLog("authEvent", "login denied: unconfirmed (pending) account for loginNameHash: {$loginNameHash}");
            ajaxExit(UNPROCESSABLE, [ "reason" => "account pending confirmation" ]);
        }
        if ($accountStatus === "suspended") {
            writeLog("authEvent", "login denied: account suspended for loginNameHash: {$loginNameHash}");
            ajaxExit(UNPROCESSABLE, [ "reason" => "account suspended" ]);
        }
        if ($accountStatus === "terminated") {
            writeLog("authEvent", "login denied: terminated account for loginNameHash: {$loginNameHash}");
            ajaxExit(__unauthenticated__, [ "reason" => "invalid login credentials" ]); // neutral, same as bad password
        }
        $autoJaxRequest->autoJaxId = $autoJaxId;
        // a credential login begins a fresh session, so start the absolute-lifetime clock at now, overriding any
        // sessionStart carried in from a leftover token the client may have sent with the login request
        $autoJaxRequest->sessionStart = time();
        resetLoginNameHashThrottle($loginNameHash);
        writeLog("authEvent", "successful login");
        // hand the client the authenticated user's own autojax-side fields, so an app can drive its UI (a
        // "logged in as", an admin-only control) and key its own per-user data without a second round trip. this
        // is identity for display, never authority: the server re-derives the user from the jwt and re-checks
        // isAdmin on every request, so a tampered client object cannot grant access. the values are the user's
        // own, so the decrypted email and display name are no cross-user leak
        try {
            $displayName = decryptValueWithOverlap($displayName_enc);
            $email       = decryptValueWithOverlap($email_enc);
        }
        catch (\Throwable $thr) {
            dieGracefully(SERVER_ERROR, "Could not decrypt user fields at login: " . $thr->getMessage());
        }
        // when the app opts into a multi-page session, mint the cross-page token, store its hash, and set the
        // httpOnly cookie. a no-op when crossPageSession is off, so the default stays pure-SPA, memory-only
        issueCrossPageSession((int)$autoJaxId, $autoJaxRequest->sessionStart);
        // when the app opts into single-session mode, record this login as the one active session. a no-op when
        // concurrentSessions is the default 1, so the default behavior (unlimited sessions) is unchanged
        seedSingleSession((int)$autoJaxId, $autoJaxRequest->sessionStart);
        ajaxExit(OKAY, [
            "sessionDurationInMinutes" => $autoJaxConfig->jwt->durationInMinutes,
            "user" => [
                "autoJaxId"   => (int)$autoJaxId,
                "displayName" => $displayName,
                "email"       => $email,
                "isAdmin"     => (bool)$isAdmin
            ]
        ]);
    }
    else {
        recordLoginNameHashFailure($loginNameHash);
        writeLog("authEvent", "attempted login with invalid credentials :: " . json_encode($_SERVER));
        ajaxExit(__unauthenticated__, [ "reason" => "invalid login credentials" ]);
    }
}

/**
 * update == keep-alive.
 *
 * extends an authenticated user's session without any side effects. the refreshed jwt is attached by ajaxExit
 * (an envelope concern), so this only has to return a no-content success. HTTP forbids a body on a 204, so the
 * refresh-with-no-content case is carried as OKAY plus a "keepAlive" marker that ajaxPost maps back to
 * NO_CONTENT on the client. the marker names the concept (keep-alive), not the service.
 *
 * @param object $autoJaxRequest {
 *     @property int    $autoJaxId  Authenticated user ID
 *     @property string $action     Must be "update"
 * }
 * @return void Exits with HTTP OKAY and refreshed JWT. ajaxPost converts this to NO_CONTENT on the client.
 */
function sessionUpdate() {
    global $autoJaxRequest;
    if (isset($autoJaxRequest->subService) ||
        ! isset($autoJaxRequest->autoJaxId) ||
        ! $autoJaxRequest->autoJaxId
    ) {
        dieGracefully(__attack__, "Invalid keep alive request :: " . json_encode($autoJaxRequest));
    }
    ajaxExit(OKAY, (object)[ "service" => "keepAlive" ]);
}

/**
 * delete == logout.
 *
 * revoke the session server-side by stamping lastRevocation = NOW(): every session jwt this user holds
 * (iat before now) then fails the revocation check in ajaxPort on its next use. because the jwt is stateless
 * there is no per-token server state to delete, so this is necessarily a global logout (all of the user's
 * sessions). it deliberately returns NO_CONTENT, which carries no refreshed token (responseCarriesSessionJwt is
 * false for 204), so the client is never handed a fresh, still-valid session as it logs out -- and a keep-alive
 * racing this logout loses, because once this commits the older token is rejected on arrival.
 *
 * @param object $autoJaxRequest {
 *     @property int    $autoJaxId  Authenticated user ID
 *     @property string $action     Must be "delete"
 * }
 * @return void Exits with HTTP NO_CONTENT, no refreshed token.
 */
function sessionDelete() {
    global $autoJaxRequest;
    if (isset($autoJaxRequest->subService) ||
        ! isset($autoJaxRequest->autoJaxId) ||
        ! $autoJaxRequest->autoJaxId
    ) {
        dieGracefully(__attack__, "Invalid logout request :: " . json_encode($autoJaxRequest));
    }
    $uid = (int)$autoJaxRequest->autoJaxId;
    try {
        // stamp lastRevocation (kills every access jwt) and NULL both cross-page hash slots (kills the
        // cross-page token's rotating single-active check) plus the single-session slot in one statement.
        // lastRevocation alone already invalidates them all by iat, the NULLs are belt and braces and remove the
        // references so a superseded token reads as "no session" rather than "wrong session"
        dbQuery(
            "logout",
            "UPDATE autoJaxUser
             SET lastRevocation = NOW(), crossPageTokenHash = NULL, crossPageTokenHashPrev = NULL,
                 currentSessionHash = NULL, currentSessionExpiration = NULL
             WHERE autoJaxUserId = ?",
            "i",
            [ $uid ]
        );
    }
    catch (\Throwable $thr) {
        writeLog("authEvent", "logout died on db failure for uid: {$uid} :: " . json_encode($_SERVER));
        dieGracefully(
            SERVER_ERROR,
            "Could not log user out: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    // expire the cross-page cookie too, so a real navigation after logout sends nothing
    setCrossPageCookie("", true);
    writeLog("authEvent", "logout: session revoked for uid: {$uid}");
    ajaxExit(NO_CONTENT);
}

/**
 * read == cross-page exchange.
 *
 * recover a memory-only session into a freshly-loaded document (a real navigation, not an in-SPA route). the
 * httpOnly cross-page cookie is the credential, so this is public in the api -- no access jwt is presented or
 * required. on success it hands back a fresh access jwt (minted by ajaxExit) plus the user object, exactly as
 * login does, which the client adopts into memory.
 *
 * every failure is the same neutral __unauthenticated__, which the client turns into "login required". the
 * checks are: the cookie exists, its token decodes and is of type cross_page and not expired, its hash matches
 * the current stored hash or the previous one within the rotation grace, the account is active, and the token
 * predates no revocation. on success the token is ROTATED: a fresh one is minted, the current hash moves to
 * the previous slot, and the new cookie is set. so a token captured from a log is the current token only until
 * the next exchange, after which it falls out of the grace window and is dead. the cookie is httpOnly, so this
 * presence-and-validity decision is necessarily the server's.
 *
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function sessionRead() {
    global $autoJaxConfig, $autoJaxRequest;
    if (isset($autoJaxRequest->subService)) {
        dieGracefully(__attack__, "Invalid cross-page exchange request :: " . json_encode($autoJaxRequest));
    }
    $token = $_COOKIE[AUTOJAX_CROSS_PAGE_COOKIE] ?? null;
    if ( ! is_string($token) || $token === "") {
        ajaxExit(__unauthenticated__, [ "reason" => "no cross-page session" ]); // no cookie -- the common case
    }
    try {
        $payload = decodeJwtWithOverlap($token, true, "cross_page");
    }
    catch (\Throwable $thr) {
        ajaxExit(__unauthenticated__, [ "reason" => "no cross-page session" ]); // forged, tampered, or undecodable
    }
    if ($payload === "expired" || ! is_object($payload) || ! isset($payload->autoJaxId, $payload->iat, $payload->exp)) {
        ajaxExit(__unauthenticated__, [ "reason" => "no cross-page session" ]);
    }
    $uid = (int)$payload->autoJaxId;
    try {
        $stmt = dbQuery(
            "crossPage/lookup",
            "SELECT crossPageTokenHash, crossPageTokenHashPrev, crossPageRotatedAt,
                    lastRevocation, accountStatus, isAdmin, displayName_enc, email_enc
             FROM autoJaxUser WHERE autoJaxUserId = ?",
            "i",
            [ $uid ]
        );
        $stmt->bind_result(
            $currentHash, $prevHash, $rotatedAt,
            $lastRevocation, $accountStatus, $isAdmin, $displayName_enc, $email_enc
        );
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "cross-page lookup failed: " . $thr->getMessage());
    }
    // rotating single-active check: the presented token's hash must equal the current stored hash, or the
    // previous one while still within the rotation grace (which only exists to tolerate the multi-tab race).
    // logout NULLs both and a re-login overwrites them, so a captured or superseded token no longer validates
    $now           = time();
    $presentedHash = hashToken($token);
    $matchesCurrent = $currentHash !== null && hash_equals($currentHash, $presentedHash);
    $matchesPrev    = $prevHash !== null
        && hash_equals($prevHash, $presentedHash)
        && $rotatedAt !== null
        && ($now - dbTimestampToEpoch($rotatedAt)) <= AUTOJAX_CROSS_PAGE_GRACE_SECONDS;
    if ( ! $found || ( ! $matchesCurrent && ! $matchesPrev)) {
        ajaxExit(__unauthenticated__, [ "reason" => "no cross-page session" ]);
    }
    if ($accountStatus !== "active") {
        ajaxExit(__unauthenticated__, [ "reason" => "account not active" ]);
    }
    // revocation: reject a token issued at or before the last revocation, mirroring the access-jwt check
    if ($lastRevocation !== null && (int)$payload->iat <= dbTimestampToEpoch($lastRevocation)) {
        ajaxExit(__unauthenticated__, [ "reason" => "cross-page session revoked" ]);
    }
    try {
        $displayName = decryptValueWithOverlap($displayName_enc);
        $email       = decryptValueWithOverlap($email_enc);
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "Could not decrypt user fields on cross-page exchange: " . $thr->getMessage());
    }
    // rotate: mint a fresh token, move the current hash into the previous slot, store the new hash, and set the
    // new cookie. the rotated token inherits the presented token's expiry (so the absolute cap from login is
    // preserved, never extended) and its original sessionStart
    $origSessionStart = (isset($payload->sessionStart) && is_int($payload->sessionStart))
        ? (int)$payload->sessionStart
        : (int)$payload->iat;
    $newToken = createJwt($uid, "cross_page", null, null, (int)$payload->exp - $now, $origSessionStart);
    try {
        dbQuery(
            "crossPage/rotate",
            "UPDATE autoJaxUser
             SET crossPageTokenHashPrev = crossPageTokenHash, crossPageTokenHash = ?, crossPageRotatedAt = NOW()
             WHERE autoJaxUserId = ?",
            "si",
            [ hashToken($newToken), $uid ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "cross-page rotation failed: " . $thr->getMessage());
    }
    setCrossPageCookie($newToken, false);
    // hand back a fresh access jwt (minted by ajaxExit) plus the user. carry the original session start so the
    // multi-page session cannot outlive the absolute maximum measured from the original login
    $autoJaxRequest->autoJaxId    = $uid;
    $autoJaxRequest->sessionStart = $origSessionStart;
    // re-seat the single-session slot onto the access jwt this exchange mints, so a recovered session stays the
    // one active session. a no-op unless concurrentSessions = 0. combining single-session with crossPageSession
    // is discouraged (see the config note): with concurrent documents/tabs each exchange re-seats the slot and
    // they will displace one another
    seedSingleSession((int)$uid, $origSessionStart);
    ajaxExit(OKAY, [
        "sessionDurationInMinutes" => $autoJaxConfig->jwt->durationInMinutes,
        "user" => [
            "autoJaxId"   => $uid,
            "displayName" => $displayName,
            "email"       => $email,
            "isAdmin"     => (bool)$isAdmin
        ]
    ]);
}

/**
 * issue a cross-page session: mint the cross-page token, store its hash on the user, and set the httpOnly
 * cookie. a no-op unless the app set crossPageSession in autojax.json, so the default behavior is unchanged.
 *
 * the token's own expiry is the session's absolute maximum, so the multi-page session can never outlive what a
 * pure-SPA session could. it begins a fresh rotation chain: the current hash is stored, the previous slot is
 * cleared, and the rotation clock starts now. from here every exchange rotates it (see sessionRead), and logout
 * or a re-login invalidate it.
 *
 * @param int $uid           the authenticated user id
 * @param int $sessionStart  the unix time the session began, carried into the token and forward to the access jwt
 * @return void
 */
function issueCrossPageSession($uid, $sessionStart) {
    global $autoJaxConfig;
    if (empty($autoJaxConfig->crossPageSession)) {
        return;
    }
    $ttlSeconds = $autoJaxConfig->jwt->absoluteMaxInMinutes * 60;
    $token      = createJwt($uid, "cross_page", null, null, $ttlSeconds, $sessionStart);
    try {
        dbQuery(
            "crossPage/issue",
            "UPDATE autoJaxUser
             SET crossPageTokenHash = ?, crossPageTokenHashPrev = NULL, crossPageRotatedAt = NOW()
             WHERE autoJaxUserId = ?",
            "si",
            [ hashToken($token), $uid ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "Could not store cross-page token: " . $thr->getMessage());
    }
    setCrossPageCookie($token, false);
}

/**
 * seed (or re-seat) the single-session slot for an account. a no-op unless the app set concurrentSessions = 0,
 * so the default unlimited-sessions mode is untouched and the slot columns stay NULL.
 *
 * mints a fresh random session id, stores its hash and the session's absolute-maximum expiry on the user row,
 * and sets the id on the request so the access jwt minted by ajaxExit carries it (and every refresh carries it
 * forward unchanged). because it overwrites the slot, a new login displaces whatever session held it before
 * (last-wins): that older session's token no longer matches the slot and is rejected on its next request.
 *
 * the stored expiry is sessionStart + the absolute maximum, the same hard cap the access jwt already enforces
 * statelessly. it lets the slot self-free lazily (a request whose slot has passed it fails the match) without a
 * cron, and can never reject a token the absolute-max check in ajaxPort would not already reject.
 *
 * @param int $uid           the authenticated user id
 * @param int $sessionStart  the unix time the session began, used to bound the slot's expiry at the absolute max
 * @return void
 */
function seedSingleSession($uid, $sessionStart) {
    global $autoJaxConfig, $autoJaxRequest;
    if (($autoJaxConfig->concurrentSessions ?? 1) !== 0) {
        return;
    }
    $sessionId = bin2hex(random_bytes(16));
    $expiry    = $sessionStart + $autoJaxConfig->jwt->absoluteMaxInMinutes * 60;
    try {
        dbQuery(
            "singleSession/seed",
            "UPDATE autoJaxUser
             SET currentSessionHash = ?, currentSessionExpiration = FROM_UNIXTIME(?)
             WHERE autoJaxUserId = ?",
            "sii",
            [ hashToken($sessionId), $expiry, $uid ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "Could not store single-session slot: " . $thr->getMessage());
    }
    $autoJaxRequest->sessionId = $sessionId;
}

/**
 * set or clear the cross-page cookie. it is httpOnly (script cannot read it, so XSS cannot exfiltrate it),
 * Secure (rides only HTTPS, which autojax requires), and SameSite=Strict (no cross-site context can send it,
 * so the exchange cannot be driven by CSRF). it is session-scoped (expires 0): gone when the browser closes,
 * never surviving a restart, which keeps the one persisted credential as short-lived as possible.
 *
 * @param string $value   the token to set (ignored when clearing)
 * @param bool   $clear   true to expire the cookie with an empty value and a past expiry
 * @return void
 */
function setCrossPageCookie($value, $clear) {
    setcookie(AUTOJAX_CROSS_PAGE_COOKIE, $clear ? "" : $value, [
        "expires"  => $clear ? time() - 3600 : 0,
        "path"     => "/",
        "secure"   => true,
        "httponly" => true,
        "samesite" => "Strict"
    ]);
}


} // end namespace AutoJax

