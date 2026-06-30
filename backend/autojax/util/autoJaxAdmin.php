#!/usr/bin/env php
<?php

/**
 * @file autoJaxAdmin.php
 *
 * CLI utility: account and admin management for an operator with shell + database access
 *
 * WHY THIS EXISTS:
 *   admin actions in the running app (admin.php) require an already-authenticated admin, so they cannot
 *   bootstrap the very first admin, nor help when every admin account is gone (departed staff, lost
 *   credentials). that has to happen out of band. this tool is that path -- creating, listing, and
 *   promoting accounts without hand-writing bcrypt hashes or raw SQL, and without ever needing a
 *   plaintext email column (accounts are listed by decrypting the stored ciphertext with the configured
 *   key). account creation here uses the same AutoJax\createUser() that self-registration uses, so a
 *   CLI-created account is encoded identically to one registered through the app.
 *
 * WHAT IT DOES:
 *   --create-admin        prompt for display name, email, and password (twice, echo off), then create an
 *                         active admin account in one step (skips the email-confirmation a self-
 *                         registration would require). this is how you make the first admin
 *   --list                list every account: id, status, isAdmin, and decrypted display name/email
 *   --grant-admin WHO     set isAdmin = 1 for the account named by WHO
 *   --revoke-admin WHO    set isAdmin = 0 (warns, but does not refuse, if it leaves zero admins --
 *                         this tool is the recovery path, so it is intentionally unguarded)
 *   --activate WHO        set accountStatus = 'active' (e.g. to confirm an account by hand when self-
 *                         registration would otherwise leave it 'pending')
 *   --set-password WHO    prompt (twice, echo off) for a new password, validate it, hash it, and store
 *                         it. also stamps lastRevocation to NOW() and clears passwordResetTokenHash so any
 *                         outstanding sessions and reset links for that account are invalidated
 *
 *   WHO may be a numeric autoJaxUserId or an email address (matched case-insensitively).
 *
 * USAGE:
 *   php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php --create-admin
 *   php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php --list
 *   php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php --grant-admin 1
 *   php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php --grant-admin user@example.com
 *   php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php --activate 1
 *   php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php --set-password 1
 *
 *   autojax.json must be in the same directory as autojax-secrets.php.
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

use function AutoJax\createUser;
use function AutoJax\dbFetchRows;
use function AutoJax\dbQuery;
use function AutoJax\decryptValueWithOverlap;
use function AutoJax\inDevAndTest;
use function AutoJax\isValidDisplayName;
use function AutoJax\isValidPassword;
use function AutoJax\lowercaseHash;
use function AutoJax\normalizeWhitespace;
use function AutoJax\readAutoJaxJson;

// ─────────────────────────────────────────────────────────────────────────────
// bootstrap
// ─────────────────────────────────────────────────────────────────────────────

if (PHP_SAPI !== "cli") {
    fwrite(STDERR, "autoJaxAdmin.php must be run from the command line\n");
    exit(1);
}

$opts = getopt("", [ "secrets:", "create-admin", "list", "grant-admin:", "revoke-admin:", "activate:", "set-password:" ]);

if (empty($opts["secrets"])) {
    fwrite(STDERR, "usage: php autoJaxAdmin.php --secrets /path/to/autojax-secrets.php [--create-admin | --list | --grant-admin WHO | --revoke-admin WHO | --activate WHO | --set-password WHO]\n");
    exit(1);
}

$_secretsFile = $opts["secrets"];
if ( ! file_exists($_secretsFile)) {
    fwrite(STDERR, "secrets file not found: {$_secretsFile}\n");
    exit(1);
}
require_once $_secretsFile; // populates $autoJaxSecrets

$_jsonPath = dirname($_secretsFile) . "/autojax.json";
if ( ! file_exists($_jsonPath)) {
    fwrite(STDERR, "autojax.json not found at: {$_jsonPath}\n");
    exit(1);
}
require_once __DIR__ . "/readAutoJaxJson.php";
$_cfg = readAutoJaxJson($_jsonPath);
unset($_secretsFile, $_jsonPath);

require_once __DIR__ . "/util.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/security.php";

$_inDebugMode  = inDevAndTest();
$autoJaxConfig = (object)[
    "db"               => (object)[
        "host"     => $autoJaxSecrets->dbHost,
        "database" => $autoJaxSecrets->dbName . ($_inDebugMode ? "Test" : ""),
        "user"     => $autoJaxSecrets->dbUser,
        "password" => $autoJaxSecrets->dbPassword
    ],
    "encryptionKey"    => $autoJaxSecrets->encryptionKey,
    "encryptionKeyOld" => $autoJaxSecrets->encryptionKeyOld,
    "encKeyLastUpdate" => $autoJaxSecrets->encKeyLastUpdate,
    "jwt"              => (object)[ "durationInMinutes" => $_cfg->jwt->durationInMinutes ],
    "password"         => (object)[ "minLength" => $_cfg->password->minLength ?? 12 ]
];
unset($_cfg, $_inDebugMode);

// ─────────────────────────────────────────────────────────────────────────────
// helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * fetch a single account's current state, or null if no such id
 *
 * @param int $id autoJaxUserId
 * @return array|null  row with autoJaxUserId, accountStatus, and isAdmin, or null
 */
function fetchAccount($id) {
    $rows = dbFetchRows(
        "autoJaxAdmin: fetch account",
        "SELECT autoJaxUserId, accountStatus, isAdmin FROM autoJaxUser WHERE autoJaxUserId = ?",
        "i",
        [ $id ]
    );
    return $rows ? $rows[0] : null;
}

/**
 * resolve a WHO argument -- a numeric autoJaxUserId or an email address -- to its account row.
 * emails match case-insensitively via the lowercased lookup hash, never a plaintext column.
 *
 * @param string $identifier  a numeric autoJaxUserId, or an email address
 * @return array|null  the account row (autoJaxUserId, accountStatus, isAdmin), or null if not found
 */
function resolveAccount($identifier) {
    $identifier = trim((string)$identifier);
    if ($identifier === "") {
        return null;
    }
    if (ctype_digit($identifier)) {
        return fetchAccount((int)$identifier);
    }
    $rows = dbFetchRows(
        "autoJaxAdmin: resolve email",
        "SELECT autoJaxUserId, accountStatus, isAdmin FROM autoJaxUser WHERE email_hash = ?",
        "s",
        [ lowercaseHash($identifier) ]
    );
    return $rows ? $rows[0] : null;
}

/**
 * count accounts with isAdmin = 1
 *
 * @return int
 */
function adminCount() {
    $rows = dbFetchRows("autoJaxAdmin: count admins", "SELECT COUNT(*) AS n FROM autoJaxUser WHERE isAdmin = 1");
    return (int)$rows[0]["n"];
}

/**
 * read a line from the terminal with echo on, normalized
 *
 * @param string $label prompt label
 * @return string the entered value, whitespace-normalized
 */
function promptLine($label) {
    fwrite(STDOUT, $label . ": ");
    return normalizeWhitespace((string)fgets(STDIN));
}

/**
 * read a password from the terminal twice with echo disabled, returning it only if both match
 *
 * @return string|null  the password, or null if the two entries did not match
 */
function promptForPassword() {
    $hasStty = false;
    $sttySave = @shell_exec("stty -g 2>/dev/null");
    if (is_string($sttySave) && trim($sttySave) !== "") {
        $hasStty = true;
        shell_exec("stty -echo 2>/dev/null");
    }
    fwrite(STDOUT, "New password: ");
    $first = rtrim((string)fgets(STDIN), "\r\n");
    fwrite(STDOUT, "\nConfirm password: ");
    $second = rtrim((string)fgets(STDIN), "\r\n");
    if ($hasStty) {
        shell_exec("stty " . escapeshellarg(trim($sttySave)) . " 2>/dev/null");
    }
    fwrite(STDOUT, "\n");
    return $first === $second ? $first : null;
}

/**
 * print a line to stdout
 *
 * @param string $msg
 * @return void
 */
function out($msg) {
    fwrite(STDOUT, $msg . "\n");
}

// ─────────────────────────────────────────────────────────────────────────────
// dispatch -- exactly one action per invocation
// ─────────────────────────────────────────────────────────────────────────────

if (isset($opts["create-admin"])) {
    // validate exactly as self-registration does, so a CLI-created account is indistinguishable from a
    // registered one. then createUser writes it active and admin in a single step
    $displayName = promptLine("Display name");
    $email       = promptLine("Email");
    if ( ! isValidDisplayName($displayName)) {
        fwrite(STDERR, "invalid display name -- no account created\n");
        exit(1);
    }
    if (strlen($email) > 254 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fwrite(STDERR, "invalid email -- no account created\n");
        exit(1);
    }
    $password = promptForPassword();
    if ($password === null) {
        fwrite(STDERR, "passwords did not match -- no account created\n");
        exit(1);
    }
    $password = normalizeWhitespace($password, true);
    if ( ! isValidPassword($password)) {
        fwrite(STDERR, "password does not meet the strength requirements -- no account created\n");
        exit(1);
    }
    try {
        $newId = createUser($displayName, $email, $password, "active", "autoJaxAdmin", true);
    }
    catch (\Throwable $thr) {
        fwrite(STDERR, "could not create account: " . $thr->getMessage() . "\n");
        exit(1);
    }
    if ($newId === null) {
        fwrite(STDERR, "an account with that display name or email already exists -- no account created\n");
        exit(1);
    }
    out("created active admin account autoJaxUserId {$newId}");
    exit(0);
}

if (isset($opts["list"])) {
    $rows = dbFetchRows(
        "autoJaxAdmin: list",
        "SELECT autoJaxUserId, displayName_enc, email_enc, accountStatus, isAdmin FROM autoJaxUser ORDER BY autoJaxUserId"
    );
    out(str_pad("id", 6) . str_pad("admin", 7) . str_pad("status", 12) . "display name / email");
    out(str_repeat("-", 72));
    foreach ($rows as $row) {
        try {
            $displayName = decryptValueWithOverlap($row["displayName_enc"]);
            $email       = decryptValueWithOverlap($row["email_enc"]);
        }
        catch (Throwable $thr) {
            $displayName = "(decrypt failed)";
            $email       = "(decrypt failed)";
        }
        out(
            str_pad((string)$row["autoJaxUserId"], 6) .
            str_pad($row["isAdmin"] ? "yes" : "no", 7) .
            str_pad($row["accountStatus"], 12) .
            "{$displayName} <{$email}>"
        );
    }
    out(str_repeat("-", 72));
    out(adminCount() . " admin account(s)");
    exit(0);
}

if (isset($opts["grant-admin"]) || isset($opts["revoke-admin"])) {
    $grant      = isset($opts["grant-admin"]);
    $identifier = $grant ? $opts["grant-admin"] : $opts["revoke-admin"];
    $account    = resolveAccount($identifier);
    if ( ! $account) {
        fwrite(STDERR, "no account for '" . trim((string)$identifier) . "'\n");
        exit(1);
    }
    $id = (int)$account["autoJaxUserId"];
    if ( ! $grant && $account["isAdmin"] && adminCount() <= 1) {
        out("WARNING: autoJaxUserId {$id} is the last admin -- revoking leaves no admins.");
        out("re-run with --grant-admin afterward, or grant another account first.");
    }
    dbQuery(
        "autoJaxAdmin: set admin",
        "UPDATE autoJaxUser SET isAdmin = ? WHERE autoJaxUserId = ?",
        "ii",
        [ $grant ? 1 : 0, $id ]
    );
    out(($grant ? "granted" : "revoked") . " admin for autoJaxUserId {$id}");
    exit(0);
}

if (isset($opts["activate"])) {
    $account = resolveAccount($opts["activate"]);
    if ( ! $account) {
        fwrite(STDERR, "no account for '" . trim((string)$opts["activate"]) . "'\n");
        exit(1);
    }
    $id = (int)$account["autoJaxUserId"];
    if ($account["accountStatus"] === "active") {
        out("autoJaxUserId {$id} is already active");
        exit(0);
    }
    dbQuery(
        "autoJaxAdmin: activate",
        "UPDATE autoJaxUser SET accountStatus = 'active', statusChangedAt = NOW(), statusChangedBy = 'autoJaxAdmin' WHERE autoJaxUserId = ?",
        "i",
        [ $id ]
    );
    out("activated autoJaxUserId {$id} (was {$account["accountStatus"]})");
    exit(0);
}

if (isset($opts["set-password"])) {
    $account = resolveAccount($opts["set-password"]);
    if ( ! $account) {
        fwrite(STDERR, "no account for '" . trim((string)$opts["set-password"]) . "'\n");
        exit(1);
    }
    $id       = (int)$account["autoJaxUserId"];
    $password = promptForPassword();
    if ($password === null) {
        fwrite(STDERR, "passwords did not match -- no change made\n");
        exit(1);
    }
    if ( ! isValidPassword($password)) {
        fwrite(STDERR, "password does not meet the strength requirements -- no change made\n");
        exit(1);
    }
    dbQuery(
        "autoJaxAdmin: set password",
        "UPDATE autoJaxUser SET password_hash = ?, lastRevocation = NOW(), passwordResetTokenHash = NULL WHERE autoJaxUserId = ?",
        "si",
        [ password_hash($password, PASSWORD_BCRYPT), $id ]
    );
    out("password updated for autoJaxUserId {$id}. existing sessions and reset links invalidated");
    exit(0);
}

fwrite(STDERR, "no action given. one of: --create-admin, --list, --grant-admin WHO, --revoke-admin WHO, --activate WHO, --set-password WHO\n");
exit(1);
