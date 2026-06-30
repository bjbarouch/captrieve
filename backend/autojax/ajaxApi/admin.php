<?php

namespace AutoJax {

/**
 * @file admin.php
 *
 * admin panel service -- account management for users with isAdmin = 1
 *
 * this file is part of autojax/core and must not be edited.
 *
 * actions:
 *   read   -- look up a single user by email address or display name (identifier hashed client-side)
 *             returns the user's decrypted name, email, accountStatus, and isAdmin, or 404 if not found
 *   update -- change a user's accountStatus or isAdmin flag
 *             $param->adminAction selects the operation:
 *               "suspendAccount"   -- set accountStatus = 'suspended', stamp lastRevocation
 *               "reviveAccount"    -- set accountStatus = 'active' (works from suspended or terminated)
 *               "terminateAccount" -- set accountStatus = 'terminated', stamp lastRevocation
 *               "grantAdmin"      -- set isAdmin = 1
 *               "revokeAdmin"     -- set isAdmin = 0. refused if target is the last admin
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

require_once __DIR__ . "/../util/adminReview.php"; // compileAdminReview(), markAdminReviewed()

/**
 * required entry point
 *
 * @param object $request {
 *     @property object $user       authenticated user object (set by ajaxPort via autoJaxFetchUserInfo)
 *     @property string $subService null for account management, "review" for the admin activity review
 *     @property string $action     "read" or "update"
 *     @property object $param      action-specific parameters
 * }
 * @return void
 */
function serviceRequest() {
    global $autoJaxRequest;
    enforceAdminAccess();
    if (($autoJaxRequest->subService ?? null) === "review") {
        if ($autoJaxRequest->action === "read") {
            handleReviewRead();
        }
        else {
            if ($autoJaxRequest->action === "update") {
                handleReviewUpdate();
            }
            else {
                dieGracefully(__attack__, "Attack: unsupported action for admin review :: " . json_encode($_SERVER));
            }
        }
        return;
    }
    if ($autoJaxRequest->action === "read") {
        handleRead();
    }
    else {
        if ($autoJaxRequest->action === "update") {
            handleUpdate();
        }
        else {
            dieGracefully(__attack__, "Attack: unsupported action for admin service :: " . json_encode($_SERVER));
        }
    }
}

/**
 * compile and return the on-demand admin activity review: server errors, other errors, and new registrations,
 * each since the admin last marked that category reviewed. only sanitized summaries and counts are returned --
 * full detail stays in the log files (see adminReview.php).
 *
 * @return void
 */
function handleReviewRead() {
    ajaxExit(OKAY, [ "review" => compileAdminReview() ]);
}

/**
 * mark one review category reviewed, resetting its "since" timestamp to now. the category is validated inside
 * markAdminReviewed(), which rejects anything that is not a known category.
 *
 * @return void
 */
function handleReviewUpdate() {
    global $autoJaxRequest;
    if ( ! isset($autoJaxRequest->param->category) || ! is_string($autoJaxRequest->param->category)) {
        dieGracefully(__attack__, "Attack: admin/review missing category :: " . json_encode($_SERVER));
    }
    $reviewedAt = markAdminReviewed($autoJaxRequest->param->category);
    ajaxExit(OKAY, [ "category" => $autoJaxRequest->param->category, "reviewedAt" => $reviewedAt ]);
}

/**
 * verify the authenticated user has isAdmin = 1
 * $autoJaxRequest->user is already hydrated by ajaxPort -- no extra DB query needed
 *
 * @return void  exits via exitUnauthorized() if check fails
 */
function enforceAdminAccess() {
    global $autoJaxRequest;
    if ( ! isset($autoJaxRequest->user) || ! $autoJaxRequest->user->isAdmin) {
        writeLog("authEvent", "admin access denied for uid: " . ($autoJaxRequest->user->autoJaxId ?? 0));
        exitUnauthorized();
    }
}

/**
 * look up a single user by email address or display name
 * the identifier is hashed client-side before sending. we never receive plaintext
 *
 * @return void
 */
function handleRead() {
    global $autoJaxRequest;
    if ( ! isset($autoJaxRequest->param->identifierHash) || ! is_string($autoJaxRequest->param->identifierHash)) {
        dieGracefully(__attack__, "Attack: admin/read missing identifierHash :: " . json_encode($_SERVER));
    }
    $identifierHash = $autoJaxRequest->param->identifierHash;
    if ( ! preg_match('/^[0-9a-f]{64}$/', $identifierHash)) {
        dieGracefully(__attack__, "Attack: admin/read invalid identifierHash :: " . json_encode($_SERVER));
    }
    try {
        $stmt = dbQuery(
            "admin/read",
            "SELECT autoJaxUserId, displayName_enc, email_enc, accountStatus, isAdmin
             FROM autoJaxUser
             WHERE email_hash = ? OR displayName_hash = ?",
            "ss",
            [ $identifierHash, $identifierHash ]
        );
        $stmt->bind_result($autoJaxUserId, $displayName_enc, $email_enc, $accountStatus, $isAdmin);
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/read db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ( ! $found) {
        // 404 carries no payload -- the caller knows it searched by identifier, so the bare code says all it needs to
        ajaxExit(NOT_FOUND);
    }
    try {
        $displayName = decryptValueWithOverlap($displayName_enc);
        $email       = decryptValueWithOverlap($email_enc);
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/read decrypt failure for autoJaxUserId {$autoJaxUserId}: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    ajaxExit(OKAY, [
        "user" => [
            "autoJaxUserId" => (int)$autoJaxUserId,
            "displayName"   => $displayName,
            "email"         => $email,
            "accountStatus" => $accountStatus,
            "isAdmin"       => (bool)$isAdmin
        ]
    ]);
}

/**
 * perform the requested account management action
 *
 * @return void
 */
function handleUpdate() {
    global $autoJaxRequest;
    if (
        ! isset($autoJaxRequest->param->adminAction, $autoJaxRequest->param->autoJaxUserId) ||
        ! is_string($autoJaxRequest->param->adminAction)                                    ||
        ! is_int($autoJaxRequest->param->autoJaxUserId)                                     ||
        $autoJaxRequest->param->autoJaxUserId < 1
    ) {
        dieGracefully(__attack__, "Attack: admin/update missing or invalid params :: " . json_encode($_SERVER));
    }
    $adminAction = $autoJaxRequest->param->adminAction;
    $targetId    = $autoJaxRequest->param->autoJaxUserId;
    $validActions = [
        "suspendAccount",
        "reviveAccount",
        "terminateAccount",
        "grantAdmin",
        "revokeAdmin"
    ];
    if ( ! in_array($adminAction, $validActions, true)) {
        dieGracefully(__attack__, "Attack: unknown adminAction: {$adminAction} :: " . json_encode($_SERVER));
    }
    // fetch current state of the target user
    try {
        $stmt = dbQuery(
            "admin/update",
            "SELECT accountStatus, isAdmin FROM autoJaxUser WHERE autoJaxUserId = ?",
            "i",
            [ $targetId ]
        );
        $stmt->bind_result($accountStatus, $isAdmin);
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/update db failure fetching target: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ( ! $found) {
        // 404 carries no payload -- the caller named the target user, so the bare code says all it needs to
        ajaxExit(NOT_FOUND);
    }
    // an admin must not lock themselves out by acting destructively on their own live account.
    // combined with the last-admin guards below, this guarantees at least one admin always remains
    if ((int)$autoJaxRequest->user->autoJaxId === $targetId &&
        in_array($adminAction, [ "suspendAccount", "terminateAccount", "revokeAdmin" ], true)
    ) {
        writeLog("authEvent", "admin/update refused self-{$adminAction} for uid: {$targetId}");
        ajaxExit(UNPROCESSABLE, [ "reason" => "cannot perform this action on your own account" ]);
    }
    switch ($adminAction) {
        case "suspendAccount" :
            suspendAccount($targetId, $accountStatus, $isAdmin);
            break;
        case "reviveAccount" :
            reviveAccount($targetId, $accountStatus);
            break;
        case "terminateAccount" :
            terminateAccount($targetId, $accountStatus, $isAdmin);
            break;
        case "grantAdmin" :
            grantAdmin($targetId, $isAdmin);
            break;
        case "revokeAdmin" :
            revokeAdmin($targetId, $isAdmin);
            break;
    }
}

/**
 * set accountStatus = 'suspended' and stamp lastRevocation to evict active sessions
 *
 * @param int    $targetId      autoJaxUserId of the account to suspend
 * @param string $accountStatus current accountStatus of the target
 * @param int    $isAdmin       current isAdmin value of the target
 * @return void
 */
function suspendAccount($targetId, $accountStatus, $isAdmin) {
    global $autoJaxRequest;
    if ($accountStatus === "suspended") {
        ajaxExit(OKAY, [ "reason" => "already suspended" ]);
    }
    if ($accountStatus === "terminated") {
        dieGracefully(__attack__, "Attack: attempt to suspend a terminated account :: " . json_encode($_SERVER));
    }
    // lock the admin set, recount, and update in one transaction so a concurrent demotion cannot race this one
    // into removing the last admin. ajaxExit is called only after the transaction is resolved, never inside it
    $refusedLastAdmin = false;
    try {
        dbBeginTransaction();
        if ($isAdmin && countAdminsLocked() <= 1) {
            $refusedLastAdmin = true;
            dbRollbackTransaction();
        }
        else {
            dbQuery(
                "admin/suspend",
                "UPDATE autoJaxUser SET
                    accountStatus   = 'suspended',
                    lastRevocation  = NOW(),
                    statusChangedAt = NOW(),
                    statusChangedBy = 'admin'
                WHERE autoJaxUserId = ?",
                "i",
                [ $targetId ]
            );
            dbCommitTransaction();
        }
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/suspend db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ($refusedLastAdmin) {
        writeLog("authEvent", "admin/suspend refused (last admin) for autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
        ajaxExit(UNPROCESSABLE, [ "reason" => "cannot suspend the last admin account" ]);
    }
    writeLog("authEvent", "admin suspended autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
    ajaxExit(NO_CONTENT);
}

/**
 * set accountStatus = 'active'
 * works from both 'suspended' and 'terminated' -- termination is reversible
 *
 * @param int    $targetId      autoJaxUserId of the account to revive
 * @param string $accountStatus current accountStatus of the target
 * @return void
 */
function reviveAccount($targetId, $accountStatus) {
    global $autoJaxRequest;
    if ($accountStatus === "active") {
        ajaxExit(OKAY, [ "reason" => "already active" ]);
    }
    try {
        dbQuery(
            "admin/revive",
            "UPDATE autoJaxUser SET
                accountStatus  = 'active',
                statusChangedAt = NOW(),
                statusChangedBy = 'admin'
            WHERE autoJaxUserId = ?",
            "i",
            [ $targetId ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/revive db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    writeLog("authEvent", "admin revived autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
    ajaxExit(NO_CONTENT);
}

/**
 * set accountStatus = 'terminated' and stamp lastRevocation to evict active sessions
 *
 * @param int    $targetId      autoJaxUserId of the account to terminate
 * @param string $accountStatus current accountStatus of the target
 * @param int    $isAdmin       current isAdmin value of the target
 * @return void
 */
function terminateAccount($targetId, $accountStatus, $isAdmin) {
    global $autoJaxRequest;
    if ($accountStatus === "terminated") {
        ajaxExit(OKAY, [ "reason" => "already terminated" ]);
    }
    // lock the admin set, recount, and update in one transaction so a concurrent demotion cannot race this one
    // into removing the last admin. ajaxExit is called only after the transaction is resolved, never inside it
    $refusedLastAdmin = false;
    try {
        dbBeginTransaction();
        if ($isAdmin && countAdminsLocked() <= 1) {
            $refusedLastAdmin = true;
            dbRollbackTransaction();
        }
        else {
            dbQuery(
                "admin/terminate",
                "UPDATE autoJaxUser SET
                    accountStatus   = 'terminated',
                    lastRevocation  = NOW(),
                    statusChangedAt = NOW(),
                    statusChangedBy = 'admin'
                WHERE autoJaxUserId = ?",
                "i",
                [ $targetId ]
            );
            dbCommitTransaction();
        }
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/terminate db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ($refusedLastAdmin) {
        writeLog("authEvent", "admin/terminate refused (last admin) for autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
        ajaxExit(UNPROCESSABLE, [ "reason" => "cannot terminate the last admin account" ]);
    }
    writeLog("authEvent", "admin terminated autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
    ajaxExit(NO_CONTENT);
}

/**
 * set isAdmin = 1
 *
 * @param int  $targetId  autoJaxUserId of the account to promote
 * @param int  $isAdmin   current isAdmin value of the target
 * @return void
 */
function grantAdmin($targetId, $isAdmin) {
    global $autoJaxRequest;
    if ($isAdmin) {
        ajaxExit(OKAY, [ "reason" => "already an admin" ]);
    }
    try {
        dbQuery(
            "admin/grantAdmin",
            "UPDATE autoJaxUser SET isAdmin = 1 WHERE autoJaxUserId = ?",
            "i",
            [ $targetId ]
        );
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/grantAdmin db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    writeLog("authEvent", "admin granted admin to autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
    ajaxExit(NO_CONTENT);
}

/**
 * count the accounts that currently have isAdmin = 1, with a locking read (FOR UPDATE).
 *
 * must be called inside an open transaction. locking the isAdmin rows serializes concurrent admin-demoting
 * operations, so two of them cannot each observe "2 admins" and collectively drop the count to zero -- a plain
 * COUNT is a non-locking snapshot under InnoDB's default isolation and would not prevent that. lets dbQuery
 * throw on failure so the caller's transaction handler deals with it.
 *
 * @return int  number of admin accounts, their rows locked until the transaction ends
 */
function countAdminsLocked() {
    $stmt = dbQuery(
        "admin/countAdmins",
        "SELECT COUNT(*) FROM autoJaxUser WHERE isAdmin = 1 FOR UPDATE",
        "",
        []
    );
    $stmt->bind_result($adminCount);
    $stmt->fetch();
    $stmt->close();
    return (int)$adminCount;
}

/**
 * set isAdmin = 0. refuses if this would leave zero admin accounts
 *
 * @param int  $targetId  autoJaxUserId of the account to demote
 * @param int  $isAdmin   current isAdmin value of the target
 * @return void
 */
function revokeAdmin($targetId, $isAdmin) {
    global $autoJaxRequest;
    if ( ! $isAdmin) {
        ajaxExit(OKAY, [ "reason" => "not an admin" ]);
    }
    // lock the admin set, recount, and demote in one transaction so two concurrent revokes cannot both pass the
    // guard and leave zero admins. ajaxExit is called only after the transaction is resolved, never inside it
    $refusedLastAdmin = false;
    try {
        dbBeginTransaction();
        if (countAdminsLocked() <= 1) {
            $refusedLastAdmin = true;
            dbRollbackTransaction();
        }
        else {
            dbQuery(
                "admin/revokeAdmin",
                "UPDATE autoJaxUser SET isAdmin = 0 WHERE autoJaxUserId = ?",
                "i",
                [ $targetId ]
            );
            dbCommitTransaction();
        }
    }
    catch (\Throwable $thr) {
        dieGracefully(
            SERVER_ERROR,
            "admin/revokeAdmin update db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ($refusedLastAdmin) {
        writeLog("authEvent", "admin/revokeAdmin refused (last admin) for autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
        ajaxExit(UNPROCESSABLE, [ "reason" => "cannot revoke the last admin account" ]);
    }
    writeLog("authEvent", "admin revoked admin from autoJaxUserId: {$targetId} by uid: {$autoJaxRequest->user->autoJaxId}");
    ajaxExit(NO_CONTENT);
}

} // end namespace AutoJax

