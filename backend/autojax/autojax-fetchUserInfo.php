<?php

use function AutoJax\dbQuery;
use function AutoJax\decryptValueWithOverlap;
use function AutoJax\dieGracefully;
use const AutoJax\SERVER_ERROR;
use const AutoJax\__attack__;

/**
 * @file autojax-fetchUserInfo.php
 *
 * builds the authenticated user object for every request that requires auth
 *
 * called by ajaxPort.php immediately after JWT verification.
 * fetches the autojax core user fields from autoJaxUser and returns them
 * as a stdClass. the object is available to every service as $autoJaxRequest->user.
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

/**
 * build the user object for a validated session uid
 *
 * fetches lastRevocation and accountStatus (required by the auth pipeline)
 * plus isAdmin, displayName, and email in a single query. it also carries the single-session slot
 * (currentSessionHash, currentSessionExpiration) so ajaxPort can enforce concurrentSessions = 0 without a
 * second query. both are NULL in the default unlimited-sessions mode and the check is then skipped.
 *
 * must never return null -- calls dieGracefully() on any failure.
 *
 * @param  int      $uid  validated user ID from the JWT payload. guaranteed > 0
 * @return stdClass
 */
function autoJaxFetchUserInfo($uid) {
    try {
        $stmt = AutoJax\dbQuery(
            "autoJaxFetchUserInfo",
            "SELECT lastRevocation, accountStatus, isAdmin, displayName_enc, email_enc,
                    currentSessionHash, currentSessionExpiration
             FROM autoJaxUser
             WHERE autoJaxUserId = ?",
            "i",
            [ $uid ]
        );
        $stmt->bind_result(
            $lastRevocation, $accountStatus, $isAdmin, $displayName_enc, $email_enc,
            $currentSessionHash, $currentSessionExpiration
        );
        $found = $stmt->fetch();
        $stmt->close();
    }
    catch (\Throwable $thr) {
        AutoJax\dieGracefully(
            AutoJax\SERVER_ERROR,
            "autoJaxFetchUserInfo db failure: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    if ( ! $found) {
        AutoJax\dieGracefully(AutoJax\__attack__, "Attack: jwt uid references unknown user :: " . json_encode($_SERVER));
    }
    try {
        $displayName = AutoJax\decryptValueWithOverlap($displayName_enc);
        $email       = AutoJax\decryptValueWithOverlap($email_enc);
    }
    catch (\Throwable $thr) {
        AutoJax\dieGracefully(
            AutoJax\SERVER_ERROR,
            "autoJaxFetchUserInfo decrypt failure for uid {$uid}: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    $user                            = (object)[
        "autoJaxId"                => $uid,
        "isAdmin"                  => (bool)$isAdmin,
        "displayName"              => $displayName,
        "email"                    => $email,
        "lastRevocation"           => $lastRevocation,
        "accountStatus"            => $accountStatus,
        "currentSessionHash"       => $currentSessionHash,
        "currentSessionExpiration" => $currentSessionExpiration
    ];
    return $user;
}
