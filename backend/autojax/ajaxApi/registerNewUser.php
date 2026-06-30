<?php

namespace AutoJax {

/**
 * @file registerNewUser.php
 *
 * user self-registration
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
 * required entry point
 *
 * @param object $autoJaxRequest {
 *     @property string   $action     Must be "create"
 *     @property string   $subService Must be null or empty
 *     @property int|null $autoJaxId     Must be absent or 0
 *     @property object   $param {
 *         @property string $displayName Display name of the user
 *         @property string $email       Email address of the user
 *         @property string $password    Plaintext (pre-hash) password
 *     }
 * }
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function serviceRequest() {
    global $autoJaxConfig, $autoJaxRequest;
    if ($autoJaxRequest->subService || $autoJaxRequest->autoJaxId || $autoJaxRequest->action !== "create") {
        dieGracefully(__attack__, "Attack: Invalid self-registration request :: " . json_encode($_SERVER));
    }
    $param = $autoJaxRequest->param; // for convenience
    if ( ! isset($param->displayName, $param->email, $param->password)) {
        dieGracefully(__attack__, "Attack: Invalid self-registration request parameters :: " . json_encode($_SERVER));
    }
    $param->displayName = normalizeWhitespace($param->displayName);
    $param->email       = normalizeWhitespace($param->email);
    // isValidDisplayName enforces the character-set rule (printable ASCII, no space, no backslash, no "@", no
    // unicode) and the 1-254 length. excluding "@" is what keeps display names disjoint from the email
    // namespace so the login/lookup match can never be ambiguous (see isValidDisplayName)
    if ( ! isValidDisplayName($param->displayName)) {
        dieGracefully(__attack__, "Attack: Invalid display name :: " . json_encode($_SERVER));
    }
    if (strlen($param->email) > 254 || ! filter_var($param->email, FILTER_VALIDATE_EMAIL)) {
        dieGracefully(__attack__, "Attack: Invalid email :: " . json_encode($_SERVER));
    }
    $param->password = normalizeWhitespace($param->password, true);
    if ( ! isValidPassword($param->password)) {
        dieGracefully(__unauthorized__, "Invalid password"); // a legit client should not have sent an invalid password
    }
    // clear out any expired "pending" accounts first, so the real owner of an email address that a
    // bad actor registered (but never confirmed) can reclaim it once the confirmation window passes
    purgeExpiredPendingAccounts();
    // delegate the credential encoding (case-insensitive lookup hash, as-typed ciphertext, bcrypt) to
    // createUser so self-registration and the autoJaxAdmin CLI write accounts identically. new
    // self-registrations start "pending" -- they become "active" only after the email owner confirms via
    // the link below. statusChangedAt marks creation time and drives expiry cleanup
    try {
        $newUid = createUser($param->displayName, $param->email, $param->password, "pending", "self-registration", false);
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "Could not register new user: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ($newUid === null) { // a unique-key collision means the display name or email is already registered
        // do not reveal which field collided -- distinguishing email from displayName would let an
        // attacker enumerate which emails and display names are already registered
        ajaxExit(UNPROCESSABLE, [ "reason" => "account already exists" ]);
    }
    // send the confirmation link. the account stays "pending" (and unusable) until the recipient
    // proves they own the address by clicking it. the admin notification is deferred to confirmEmail.php
    // so admins are told about real users, not unconfirmed (possibly spammed) registrations
    $ttlMinutes = isset($autoJaxConfig->emailConfirm->ttlInMinutes)
        ? (int)$autoJaxConfig->emailConfirm->ttlInMinutes
        : 10;
    try {
        $token = createJwt($newUid, "email_confirm", null, null, $ttlMinutes * 60);
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "registerNewUser could not create confirmation token: " . $thr->getMessage());
    }
    // token rides in the URL fragment (#token) -- never the query string, so it stays out of server logs
    $confirmUrl   = $autoJaxConfig->emailConfirmUrl . "#" . $token;
    $expiresInMin = $ttlMinutes;
    $body         =
        "# Confirm your account\n\n" .
        "Welcome! Please confirm your account by opening the link below.\n\n" .
        "This link expires in **{$expiresInMin} minutes**. If it expires before you use it, just register again.\n\n" .
        $confirmUrl . "\n\n" .
        "If you did not request this account, you can ignore this email.";
    captureTestToken("email_confirm", $newUid, $param->email, $token, $confirmUrl);
    // hand the confirmation link to the mail system. unlike the operator notifications this is a
    // transactional message the user is waiting on, so it is not gated by the notification cap. the per-IP
    // registration throttle already bounds how fast anyone can trigger it. if the mail system refuses the
    // handoff the account can never be confirmed, so roll back the just-created pending row (letting the user
    // retry cleanly) and tell the client the mail server is unavailable. the 503 carries a payload, so the
    // client surfaces it rather than retrying (see ajaxPost.js). a successful handoff is not a delivery
    // guarantee, only that the local mail system accepted the message
    try {
        $emailResult = sendEmail("Confirm your account", $body, $param->email);
    }
    catch (\Throwable $thr) {
        error_log("registerNewUser: confirmation email failed: " . $thr->getMessage());
        $emailResult = SERVER_ERROR;
    }
    if ($emailResult !== NO_CONTENT) {
        try {
            dbQuery(
                "registerNewUser: roll back unconfirmable pending account",
                "DELETE FROM autoJaxUser WHERE autoJaxUserId = ? AND accountStatus = 'pending'",
                "i",
                [ $newUid ]
            );
        }
        catch (\Throwable $thr) {
            reportError(SERVER_ERROR, "registerNewUser could not roll back pending account {$newUid}: " . $thr->getMessage());
        }
        ajaxExit(SERVICE_UNAVAILABLE, [ "reason" => "email server unavailable" ]);
    }
    ajaxExit(NO_CONTENT);
}

} // end namespace AutoJax

