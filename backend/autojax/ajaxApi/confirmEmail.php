<?php

namespace AutoJax {


/**
 * @file confirmEmail.php
 *
 * self-registration email confirmation -- the second half of the registerNewUser handshake.
 *
 * registerNewUser creates the account in the "pending" state and emails a link carrying an opaque
 * "email_confirm" JWT (in the URL fragment). this service consumes that token:
 *   - signature and type ("email_confirm") are verified, and expiry is enforced by the token's own
 *     exp (set from emailConfirm.ttlInMinutes), so no server-side token store is needed
 *   - on success a "pending" account becomes "active". re-confirming an already-active account is a
 *     no-op success (idempotent)
 *   - if the account is gone (its pending window expired and it was purged) the link is rejected
 *
 * unauthenticated by definition (the registrant is not logged in). action is "update".
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

/**
 * required entry point
 *
 * @param object $autoJaxRequest {
 *     @property string $action  Must be "update"
 *     @property object $param {
 *         @property string $token  the email_confirm JWT from the confirmation link
 *     }
 * }
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function serviceRequest() {
    global $autoJaxConfig, $autoJaxRequest, $clientIpAddress;
    if (isset($autoJaxRequest->subService) || $autoJaxRequest->action !== "update") {
        dieGracefully(__attack__, "Attack: invalid confirmEmail request :: " . json_encode($_SERVER));
    }
    if ( ! isset($autoJaxRequest->param->token) || ! is_string($autoJaxRequest->param->token)) {
        dieGracefully(__attack__, "Attack: confirmEmail missing token :: " . json_encode($_SERVER));
    }
    $token = $autoJaxRequest->param->token;
    try {
        $payload = decodeJwtWithOverlap($token, true, "email_confirm");
    }
    catch (\Throwable $thr) {
        dieGracefully(__attack__, "Attack: invalid email confirmation token from ip: {$clientIpAddress} -- " . $thr->getMessage());
    }
    if ($payload === "expired") {
        // UNPROCESSABLE, not __unauthenticated__: the latter makes ajaxPost show a "session expired"
        // dialog and redirect to logout, which is wrong on a page where no session exists. the reason is
        // deliberately identical to every other confirmation failure below, so a caller cannot tell an
        // expired-but-real link from a never-valid one and learn whether an account exists or its state.
        ajaxExit(UNPROCESSABLE, [ "reason" => "confirmation link expired or invalid" ]);
    }
    if ( ! is_object($payload)) {
        dieGracefully(__attack__, "Attack: confirmEmail non-object payload :: " . json_encode($_SERVER));
    }
    $autoJaxId = $payload->autoJaxId;
    // sweep expired pending accounts before the lookup, so a too-late confirmation reads as "gone"
    purgeExpiredPendingAccounts();
    try {
        $stmt = dbQuery(
            "confirmEmail: fetch",
            "SELECT accountStatus FROM autoJaxUser WHERE autoJaxUserId = ?",
            "i",
            [ $autoJaxId ]
        );
        $stmt->bind_result($accountStatus);
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "confirmEmail db failure fetching account: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ( ! $found) {
        // account never existed, or its pending window expired and it was purged
        ajaxExit(UNPROCESSABLE, [ "reason" => "confirmation link expired or invalid" ]);
    }
    if ($accountStatus === "active") {
        ajaxExit(NO_CONTENT); // already confirmed -- idempotent success
    }
    if ($accountStatus !== "pending") {
        // suspended or terminated -- do not silently reactivate via a confirmation link. same generic reason
        // as the other failures so the response does not reveal that the account exists or what state it is in.
        ajaxExit(UNPROCESSABLE, [ "reason" => "confirmation link expired or invalid" ]);
    }
    try {
        dbQuery(
            "confirmEmail: activate",
            "UPDATE autoJaxUser SET
                accountStatus   = 'active',
                statusChangedAt = NOW(),
                statusChangedBy = 'email confirmation'
            WHERE autoJaxUserId = ? AND accountStatus = 'pending'",
            "i",
            [ $autoJaxId ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "confirmEmail db failure activating account: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    writeLog("authEvent", "email confirmed; account activated for uid: {$autoJaxId}");
    // no admin notification is sent for a new user -- the activation is recorded in the authEvent log above,
    // which is where an interested admin reviews new registrations
    ajaxExit(NO_CONTENT);
}


} // end namespace AutoJax

