<?php

namespace AutoJax;

/**
 * @file authThrottling.php
 *
 * IP and login-name throttling
 * checks and updates fail counts within a rolling time window
 *
 * IP throttling applies to all requests and is called from ajaxPort.php (the general and public buckets)
 * and from the password reset service (password.php, its two reset buckets). session.php does not touch the
 * IP throttle, only the login-name throttle below. Each throttle bucket is independent -- login failures
 * do not affect password reset counters and vice versa.
 *
 * login-name throttling applies only to login attempts and is called from session.php. it lives in
 * autoJaxThrottle too, keyed by the login-name hash (throttleType "login_name") rather than by IP,
 * so it tracks failures per identity. keying on the hash means a counter exists whether or not the
 * login name maps to a real account, so it does not leak account existence.
 *
 * throttle type constants:
 *   THROTTLE_LOGIN                    - login attempts per IP
 *   THROTTLE_LOGIN_NAME               - login attempts per login-name hash
 *   THROTTLE_PUBLIC                   - unauthenticated public-endpoint requests per IP (login, password, registerNewUser)
 *   THROTTLE_CLIENT_LOG               - client error-log submissions per IP, kept separate from THROTTLE_PUBLIC so a
 *                                       burst of client error reports cannot exhaust the bucket that also gates login
 *   THROTTLE_PASSWORD_RESET_REQUEST   - password reset email requests per IP
 *   THROTTLE_PASSWORD_RESET_ATTEMPT   - password reset token submissions per IP
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

const THROTTLE_LOGIN                  = "login";
const THROTTLE_LOGIN_NAME             = "login_name"; // keyed by login-name hash, not IP (see login-name section)
const THROTTLE_PASSWORD_RESET_REQUEST = "password_reset_request";
const THROTTLE_PASSWORD_RESET_ATTEMPT = "password_reset_attempt";
const THROTTLE_PUBLIC                 = "public";     // per-IP volume cap for unauthenticated public endpoints (login, password, registerNewUser)
const THROTTLE_CLIENT_LOG             = "client_log"; // per-IP volume cap for client error logging, separate from THROTTLE_PUBLIC

// clientLog has its own per-IP volume cap, read from autojax.json (clientLogThrottle), with the constants below as
// fallback defaults. it is a separate bucket to keep the public logging path self-contained.
// it is deliberately generous: a page hitting an error loop should not get locked out, but unbounded spam is still
// capped. crucially it is a different bucket from THROTTLE_PUBLIC, so client error storms cannot lock a user out of login
const CLIENT_LOG_MAX_FAILS      = 120;
const CLIENT_LOG_WINDOW_MINUTES = 15;

// the public endpoints (login, password, registerNewUser) share one per-IP volume cap. unlike the login
// FAILURE throttle, this counts every request, success or failure, so its limit is decoupled from -- and far
// more generous than -- the login failure threshold. many legitimate users can share one public IP (office
// NAT, school, mobile CGNAT) and each makes a few calls, so a tight cap would punish them. config-driven via
// publicThrottle, falling back to these defaults. tune to where request volume actually threatens performance
// or signals abuse, not to the much lower failure thresholds
const PUBLIC_MAX_FAILS      = 300;
const PUBLIC_WINDOW_MINUTES = 15;

// -------------------------------------------------------------------------
// IP throttling (autoJaxThrottle table, keyed by ip + throttleType)
// -------------------------------------------------------------------------

/**
 * resets the failure count and window for the given IP and throttle type.
 *
 * @param string $clientIpAddress
 * @param string $throttleType  one of the THROTTLE_* constants
 * @return void
 */
function resetIpThrottle($clientIpAddress, $throttleType = THROTTLE_LOGIN) {
    try {
        dbQuery(
            "authThrottle",
            "DELETE FROM autoJaxThrottle WHERE ip = ? AND throttleType = ?",
            "ss",
            [ $clientIpAddress, $throttleType ]
        );
    }
    catch (\Throwable $thr) {
        reportError(SERVER_ERROR, "resetIpThrottle db failure: " . $thr->getMessage());
    }
}

/**
 * increments the failure count for the given IP and throttle type, starting a new window if none exists.
 *
 * @param string $clientIpAddress
 * @param string $throttleType  one of the THROTTLE_* constants
 * @return void
 */
function recordIpAddress($clientIpAddress, $throttleType = THROTTLE_LOGIN) {
    try {
        dbQuery(
            "authThrottle",
            "INSERT INTO autoJaxThrottle (ip, throttleType, failCount, windowStart)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE failCount = failCount + 1",
            "ss",
            [ $clientIpAddress, $throttleType ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "recordIpAddress db failure: " . $thr->getMessage());
    }
}

/**
 * checks whether the given IP has exceeded the failure threshold for the given throttle type.
 *
 * thresholds and window duration are read from config based on throttleType:
 *   THROTTLE_LOGIN                  -> $autoJaxConfig->loginThrottle (maxIpFails)
 *   THROTTLE_LOGIN_NAME             -> $autoJaxConfig->loginThrottle (maxLoginNameHashFails), keyed by login-name hash
 *   THROTTLE_PASSWORD_RESET_REQUEST -> $autoJaxConfig->passwordResetRequestThrottle (maxIpFails)
 *   THROTTLE_PASSWORD_RESET_ATTEMPT -> $autoJaxConfig->passwordResetAttemptThrottle (maxIpFails)
 *   THROTTLE_CLIENT_LOG             -> $autoJaxConfig->clientLogThrottle (maxIpFails), default CLIENT_LOG_MAX_FAILS / CLIENT_LOG_WINDOW_MINUTES
 *   THROTTLE_PUBLIC                 -> $autoJaxConfig->publicThrottle (maxIpFails), default PUBLIC_MAX_FAILS / PUBLIC_WINDOW_MINUTES
 *   any unlisted type              -> falls back to $autoJaxConfig->loginThrottle (maxIpFails)
 *
 * @param string $clientIpAddress
 * @param string $throttleType  one of the THROTTLE_* constants
 * @return bool
 */
function isIpThrottled($clientIpAddress, $throttleType = THROTTLE_LOGIN) {
    global $autoJaxConfig;
    if ($throttleType === THROTTLE_PUBLIC && inAutomatedTesting()) {
        // a serial automated-test run drives every request from one IP, which trips the public volume cap -- a
        // control meant for an abusive IP, not a test harness. exempt only THROTTLE_PUBLIC. the failure-based
        // throttles (login, login-name, password reset) stay active, so their behavior is still exercised
        return false;
    }
    switch ($throttleType) {
        case THROTTLE_PASSWORD_RESET_REQUEST:
            $throttleConfig = $autoJaxConfig->passwordResetRequestThrottle;
            $maxFails       = $throttleConfig->maxIpFails;
            break;
        case THROTTLE_PASSWORD_RESET_ATTEMPT:
            $throttleConfig = $autoJaxConfig->passwordResetAttemptThrottle;
            $maxFails       = $throttleConfig->maxIpFails;
            break;
        case THROTTLE_LOGIN_NAME:
            // keyed by login-name hash rather than IP, with the per-identity threshold
            $throttleConfig = $autoJaxConfig->loginThrottle;
            $maxFails       = $throttleConfig->maxLoginNameHashFails;
            break;
        case THROTTLE_CLIENT_LOG:
            // its own bucket so a client error storm cannot exhaust the public bucket that gates login.
            // config-driven via clientLogThrottle, falling back to the generous CLIENT_LOG_* defaults.
            // windowInMinutes is carried through for the expiry check below
            $clientLogCfg   = $autoJaxConfig->clientLogThrottle ?? null;
            $throttleConfig = (object)[ "windowInMinutes" => $clientLogCfg->windowInMinutes ?? CLIENT_LOG_WINDOW_MINUTES ];
            $maxFails       = $clientLogCfg->maxIpFails ?? CLIENT_LOG_MAX_FAILS;
            break;
        case THROTTLE_PUBLIC:
            // its own per-IP volume cap, decoupled from the login FAILURE threshold. it counts every public
            // request, not just failures, so it is deliberately generous to spare legitimate users who share a
            // public IP. config-driven via publicThrottle, falling back to the PUBLIC_* defaults
            $publicCfg      = $autoJaxConfig->publicThrottle ?? null;
            $throttleConfig = (object)[ "windowInMinutes" => $publicCfg->windowInMinutes ?? PUBLIC_WINDOW_MINUTES ];
            $maxFails       = $publicCfg->maxIpFails ?? PUBLIC_MAX_FAILS;
            break;
        default:
            $throttleConfig = $autoJaxConfig->loginThrottle;
            $maxFails       = $throttleConfig->maxIpFails;
    }
    try {
        $stmt = dbQuery(
            "authThrottle",
            "SELECT failCount, windowStart FROM autoJaxThrottle WHERE ip = ? AND throttleType = ?",
            "ss",
            [ $clientIpAddress, $throttleType ]
        );
        $stmt->bind_result($failCount, $windowStart);
        if ( ! $stmt->fetch()) {
            $stmt->close();
            return false; // no record yet -- not throttled
        }
        $stmt->close();
        $windowAgeInMinutes = (time() - dbTimestampToEpoch($windowStart)) / 60;
        if ($windowAgeInMinutes >= $throttleConfig->windowInMinutes) {
            writeLog("authEvent", "throttle window expired ({$throttleType}) with {$failCount} failures for key: {$clientIpAddress}");
            resetIpThrottle($clientIpAddress, $throttleType);
            return false;
        }
        if ($failCount >= $maxFails) {
            writeLog("authEvent", "throttle threshold exceeded ({$throttleType}) for key: {$clientIpAddress}");
            return true;
        }
        return false;
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "isIpThrottled db failure: " . $thr->getMessage());
    }
}

// -------------------------------------------------------------------------
// login-name throttling (autoJaxThrottle, throttleType = "login_name", keyed by login-name hash)
//
// these delegate to the IP-throttle functions above, passing the login-name hash as the key. keying
// on the hash -- rather than on per-account columns -- means a throttle counter exists whether or not
// the login name maps to a real account, which closes the account-existence oracle the old per-user-
// column scheme created (counters used to exist only for real accounts). rows for nonexistent names
// are bounded by purgeExpiredThrottleRows(). the per-identity threshold is loginThrottle.maxLoginNameHashFails.
// -------------------------------------------------------------------------

/**
 * resets the failure count and window for the given loginNameHash.
 *
 * @param string $loginNameHash  SHA-256 hash of the lowercased login name
 * @return void
 */
function resetLoginNameHashThrottle($loginNameHash) {
    if ( ! is_string($loginNameHash) || ! preg_match("/^[0-9a-f]{64}$/", $loginNameHash)) {
        return; // not a legit hash -- benign no-op
    }
    resetIpThrottle($loginNameHash, THROTTLE_LOGIN_NAME);
}

/**
 * increments the failure count for the given loginNameHash.
 *
 * @param string $loginNameHash  SHA-256 hash of the lowercased login name
 * @return void
 */
function recordLoginNameHashFailure($loginNameHash) {
    if ( ! is_string($loginNameHash) || ! preg_match("/^[0-9a-f]{64}$/", $loginNameHash)) {
        return; // not a legit hash -- benign no-op
    }
    recordIpAddress($loginNameHash, THROTTLE_LOGIN_NAME);
}

/**
 * checks whether the given loginNameHash has exceeded the failure threshold within its current window.
 *
 * @param string $loginNameHash  SHA-256 hash of the lowercased login name
 * @return bool
 */
function isLoginNameHashThrottled($loginNameHash) {
    if ( ! is_string($loginNameHash) || ! preg_match("/^[0-9a-f]{64}$/", $loginNameHash)) {
        return false;
    }
    return isIpThrottled($loginNameHash, THROTTLE_LOGIN_NAME);
}

// -------------------------------------------------------------------------
// pending-account expiry (lazy cleanup)
// -------------------------------------------------------------------------

/**
 * delete self-registered accounts still "pending" past the confirmation window.
 *
 * shared hosts cannot run scheduled jobs, so expiry is swept opportunistically by the code paths
 * that care: registerNewUser (so the real owner can reclaim an email a bad actor squatted) and login.
 * the window is emailConfirm.ttlInMinutes -- the same lifetime as the confirmation token -- measured
 * from statusChangedAt, which is set to NOW() when the pending row is created. comparing NOW() to
 * statusChangedAt keeps both sides on DB time, avoiding any PHP/DB timezone skew.
 *
 * @return void
 */
function purgeExpiredPendingAccounts() {
    global $autoJaxConfig;
    $ttlMinutes = isset($autoJaxConfig->emailConfirm->ttlInMinutes)
        ? (int)$autoJaxConfig->emailConfirm->ttlInMinutes
        : 10;
    try {
        // $ttlMinutes is an integer from config (cast above), so it is safe to inline here
        dbQuery(
            "purge expired pending accounts",
            "DELETE FROM autoJaxUser
             WHERE accountStatus = 'pending'
               AND statusChangedAt IS NOT NULL
               AND statusChangedAt < (NOW() - INTERVAL {$ttlMinutes} MINUTE)",
            "",
            []
        );
    }
    catch (\Throwable $thr) {
        reportError(SERVER_ERROR, "purgeExpiredPendingAccounts db failure: " . $thr->getMessage());
    }
}

/**
 * sweep throttle rows whose window has fully expired, bounding table growth.
 *
 * isIpThrottled() deletes an expired row only when that exact key is re-checked. login_name rows are
 * created for every attempted login name, including nonexistent ones, so an attacker probing many
 * names would leave rows that are never re-checked. this opportunistic sweep (called from login)
 * removes any row older than the largest configured window -- safely past every bucket's window.
 *
 * @return void
 */
function purgeExpiredThrottleRows() {
    global $autoJaxConfig;
    $maxMinutes = max(
        (int)$autoJaxConfig->loginThrottle->windowInMinutes,
        (int)$autoJaxConfig->passwordResetRequestThrottle->windowInMinutes,
        (int)$autoJaxConfig->passwordResetAttemptThrottle->windowInMinutes
    );
    $maxSeconds = $maxMinutes * 60;
    try {
        // $maxSeconds is an integer derived from config (cast above), so it is safe to inline here
        dbQuery(
            "purge expired throttle rows",
            "DELETE FROM autoJaxThrottle WHERE windowStart < (NOW() - INTERVAL {$maxSeconds} SECOND)",
            "",
            []
        );
    }
    catch (\Throwable $thr) {
        reportError(SERVER_ERROR, "purgeExpiredThrottleRows db failure: " . $thr->getMessage());
    }
}
