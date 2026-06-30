#!/usr/bin/env php
<?php

/**
 * @file reEncryptAll.php
 *
 * CLI utility: re-encrypt all encrypted database columns after updating the encryption key
 *
 * when the encryptionKey in autojax-secrets.php is changed, all existing ciphertext in the database
 * was written under the old key. this utility reads every row containing encrypted columns, decrypts
 * with the old key, and re-encrypts with the new key, so that the old key can be retired.
 *
 * WHEN TO RUN:
 *   after changing encryptionKey and deploying the updated autojax-secrets.php (with encryptionKeyOld
 *   and encKeyLastUpdate set). run this before the grace window expires so that decryptValueWithOverlap()
 *   does not need the old key indefinitely. once this utility completes successfully, encryptionKeyOld
 *   and encKeyLastUpdate can be cleared from autojax-secrets.php.
 *
 * SAFETY:
 *   - runs in a dry-run mode by default. pass --commit to write changes
 *   - wraps each table in a transaction. rolls back and halts on any error
 *   - counts and reports rows processed, rows skipped (already new key), and rows updated
 *   - never writes a row whose re-encryption did not fully succeed
 *
 * USAGE:
 *   php reEncryptAll.php --secrets /path/to/autojax-secrets.php [--commit]
 *
 *   autojax.json must be in the same directory as autojax-secrets.php.
 *
 * HOW TO EXTEND FOR APPLICATION-SPECIFIC TABLES:
 *   search for "TEMPLATE" in this file. each marked section describes what to fill in.
 *   autoJaxUser is fully implemented as the reference case. add additional table blocks
 *   following the same pattern.
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

use function AutoJax\dbBeginTransaction;
use function AutoJax\dbCommitTransaction;
use function AutoJax\dbFetchRows;
use function AutoJax\dbQuery;
use function AutoJax\decryptValue;
use function AutoJax\encryptValue;
use function AutoJax\inDevAndTest;
use function AutoJax\readAutoJaxJson;

// ─────────────────────────────────────────────────────────────────────────────
// bootstrap
// ─────────────────────────────────────────────────────────────────────────────

if (PHP_SAPI !== "cli") {
    fwrite(STDERR, "reEncryptAll.php must be run from the command line\n");
    exit(1);
}

$opts = getopt("", ["secrets:", "commit"]);

if (empty($opts["secrets"])) {
    fwrite(STDERR, "usage: php reEncryptAll.php --secrets /path/to/autojax-secrets.php [--commit]\n");
    exit(1);
}

$dryRun = ! isset($opts["commit"]);

$_secretsFile = $opts["secrets"];
if ( ! file_exists($_secretsFile)) {
    fwrite(STDERR, "secrets file not found: " . $_secretsFile . "\n");
    exit(1);
}

require_once $_secretsFile; // populates $autoJaxSecrets

// autojax.json lives in the same directory as autojax-secrets.php
$_jsonPath = dirname($_secretsFile) . "/autojax.json";
if ( ! file_exists($_jsonPath)) {
    fwrite(STDERR, "autojax.json not found at: " . $_jsonPath . "\n");
    exit(1);
}
require_once __DIR__ . "/readAutoJaxJson.php";
$_cfg = readAutoJaxJson($_jsonPath);
unset($_secretsFile, $_jsonPath);

require_once __DIR__ . "/util.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/security.php";

// build the minimal $autoJaxConfig needed by db.php and security.php
$_inDebugMode = inDevAndTest();
$autoJaxConfig = (object)[
    "db"             => (object)[
        "host"     => $autoJaxSecrets->dbHost,
        "database" => $autoJaxSecrets->dbName . ($_inDebugMode ? "Test" : ""),
        "user"     => $autoJaxSecrets->dbUser,
        "password" => $autoJaxSecrets->dbPassword
    ],
    "encryptionKey"      => $autoJaxSecrets->encryptionKey,
    "encryptionKeyOld"   => $autoJaxSecrets->encryptionKeyOld,
    "encKeyLastUpdate"   => $autoJaxSecrets->encKeyLastUpdate,
    "jwt"                => (object)[
        "durationInMinutes" => $_cfg->jwt->durationInMinutes,
        "secret"            => $autoJaxSecrets->jwtSecret,
        "secretOld"         => $autoJaxSecrets->jwtSecretOld,
        "secretLastUpdate"  => $autoJaxSecrets->jwtSecretLastUpdate
    ]
];
unset($_cfg, $_inDebugMode);

// ─────────────────────────────────────────────────────────────────────────────
// validate that both keys are present and distinct
// ─────────────────────────────────────────────────────────────────────────────

$newKey = $autoJaxConfig->encryptionKey    ?? null;
$oldKey = $autoJaxConfig->encryptionKeyOld ?? null;

if ( ! $newKey || ! $oldKey) {
    fwrite(STDERR, "both encryptionKey and encryptionKeyOld must be set in autojax-secrets.php\n");
    fwrite(STDERR, "encryptionKeyOld is the key that was in use before the rotation\n");
    exit(1);
}

if ($newKey === $oldKey) {
    fwrite(STDERR, "encryptionKey and encryptionKeyOld are identical -- nothing to re-encrypt\n");
    exit(1);
}

// ─────────────────────────────────────────────────────────────────────────────
// helpers
// ─────────────────────────────────────────────────────────────────────────────

/**
 * decrypt with the old key, re-encrypt with the new key
 * returns the new ciphertext blob (binary, not base64url) or throws
 *
 * @param string $blob   binary ciphertext blob
 * @param string $oldKey hex key that was used to encrypt
 * @param string $newKey hex key to encrypt with now
 * @return string        new binary ciphertext blob
 */
function reEncryptBlob($blob, $oldKey, $newKey) {
    $plaintext = decryptValue($blob, false, $oldKey); // throws if wrong key or corrupt
    return encryptValue($plaintext, false, $newKey);  // throws if key invalid
}

/**
 * print a line to stdout with a timestamp prefix
 *
 * @param string $msg
 * @return void
 */
function logLine($msg) {
    echo "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// table re-encryption blocks
//
// each block follows this pattern:
//   1. SELECT the primary key and all encrypted columns (binary fetch -- no b64 here)
//   2. for each row, attempt to decrypt each column with the old key
//      - if decryption with the old key succeeds: this row needs updating
//      - if decryption with the old key fails but succeeds with the new key: already done, skip
//      - if both fail: halt -- the value is corrupt or was encrypted with a third key
//   3. re-encrypt each column with the new key
//   4. UPDATE the row inside a transaction, roll back and halt on any error
// ─────────────────────────────────────────────────────────────────────────────

/**
 * re-encrypt one encrypted column blob for a single row, classifying it as:
 *   "updated"  -- was encrypted with oldKey. re-encrypted with newKey. $newBlob is set
 *   "skipped"  -- already encrypted with newKey. no update needed
 *   "error"    -- neither key decrypts it. $errorMessage is set
 *
 * @param string      $blob         binary ciphertext blob
 * @param string      $oldKey       previous encryption key (hex)
 * @param string      $newKey       current encryption key (hex)
 * @param string|null &$newBlob     set on "updated" result
 * @param string|null &$errorMessage set on "error" result
 * @return string "updated" | "skipped" | "error"
 */
function classifyAndReEncrypt($blob, $oldKey, $newKey, &$newBlob, &$errorMessage) {
    $newBlob      = null;
    $errorMessage = null;
    try {
        $plaintext = decryptValue($blob, false, $oldKey);
        $newBlob   = encryptValue($plaintext, false, $newKey);
        return "updated";
    }
    catch (Throwable $thr) {
        // old key did not work -- check if it is already the new key
    }
    try {
        decryptValue($blob, false, $newKey);
        return "skipped"; // already re-encrypted
    }
    catch (Throwable $thr) {
        $errorMessage = $thr->getMessage();
        return "error";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// autoJaxUser -- reference implementation
//
// encrypted columns: displayName_enc, email_enc
// primary key: autoJaxUserId (int)
// stored as: binary blob (VARBINARY) -- not base64url
// ────────────────────────────────────────────────────────────────────────────

logLine("processing table: autoJaxUser");

$totalRows   = 0;
$updatedRows = 0;
$skippedRows = 0;

try {
    $rows = dbFetchRows(
        "reEncryptAll: fetch autoJaxUser",
        "SELECT autoJaxUserId, displayName_enc, email_enc FROM autoJaxUser"
    );
}
catch (Throwable $thr) {
    fwrite(STDERR, "failed to fetch rows from autoJaxUser: " . $thr->getMessage() . "\n");
    exit(1);
}

foreach ($rows as $row) {
    ++$totalRows;
    $id               = $row["autoJaxUserId"];
    $newDisplayName   = null;
    $newEmail         = null;
    $needsUpdate      = false;
    // classify and re-encrypt displayName_enc
    $result = classifyAndReEncrypt($row["displayName_enc"], $oldKey, $newKey, $newDisplayName, $errMsg);
    if ($result === "error") {
        fwrite(STDERR, "autoJaxUserId " . $id . ": displayName_enc could not be decrypted with either key: " . $errMsg . "\n");
        fwrite(STDERR, "halting -- resolve this row before re-running\n");
        exit(1);
    }
    if ($result === "updated") {
        $needsUpdate = true;
    }
    // classify and re-encrypt email_enc
    $result = classifyAndReEncrypt($row["email_enc"], $oldKey, $newKey, $newEmail, $errMsg);
    if ($result === "error") {
        fwrite(STDERR, "autoJaxUserId " . $id . ": email_enc could not be decrypted with either key: " . $errMsg . "\n");
        fwrite(STDERR, "halting -- resolve this row before re-running\n");
        exit(1);
    }
    if ($result === "updated") {
        $needsUpdate = true;
    }
    if ( ! $needsUpdate) {
        ++$skippedRows;
        continue;
    }
    if ($dryRun) {
        logLine("dry-run: would update autoJaxUserId " . $id);
        ++$updatedRows;
        continue;
    }
    try {
        dbBeginTransaction();
        dbQuery(
            "reEncryptAll: update autoJaxUser",
            "UPDATE autoJaxUser SET displayName_enc = ?, email_enc = ? WHERE autoJaxUserId = ?",
            "ssi",
            [ $newDisplayName, $newEmail, $id ]
        );
        dbCommitTransaction();
        ++$updatedRows;
    }
    catch (Throwable $thr) {
        AutoJax\dbRollbackTransaction();
        fwrite(STDERR, "failed to update autoJaxUserId " . $id . ": " . $thr->getMessage() . "\n");
        fwrite(STDERR, "halting -- database is consistent up to this point; re-run to resume\n");
        exit(1);
    }
}

logLine(
    "autoJaxUser: " .
    $totalRows . " rows examined, " .
    $updatedRows . " updated, " .
    $skippedRows . " already current"
);

// ─────────────────────────────────────────────────────────────────────────────
// TEMPLATE: add application-specific table blocks here
//
// copy the autoJaxUser block above and adapt it for each additional table that
// has encrypted columns. the things to change in each copy are:
//
//   1. the table name -- replace "autoJaxUser" with your table name
//      (either a string literal or a config property you add to autojax-secrets.php)
//
//   2. the SELECT column list -- list your primary key column(s) and all *_enc columns
//
//   3. the column classification loop -- one classifyAndReEncrypt() call per *_enc column,
//      referencing the correct $row["column_name"] key
//
//   4. the UPDATE statement -- list every *_enc column being updated, and match the
//      bind types and params array to your primary key type(s) and column count
//
//   5. the log line -- update the table name and primary key label for clarity
//
// tables to add (fill in as the schema grows):
//
//   [ example ]
//   table: myAppTable
//   primary key: idMyAppRecord (int)
//   encrypted columns: sensitiveField_enc, otherField_enc
//
// ─────────────────────────────────────────────────────────────────────────────

// ─────────────────────────────────────────────────────────────────────────────
// summary
// ─────────────────────────────────────────────────────────────────────────────

if ($dryRun) {
    logLine("dry-run complete -- no data was written; re-run with --commit to apply");
}
else {
    logLine("re-encryption complete");
    logLine("next step: clear encryptionKeyOld and encKeyLastUpdate in autojax-secrets.php");
}
