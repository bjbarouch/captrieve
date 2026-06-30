<?php

namespace AutoJax;

/**
 * @file adminReview.php
 *
 * On-demand admin review of recent activity, compiled from the log files rather than emailed. Replaces the
 * old push-email error digest: an admin opens the admin page and sees, for each category, what has happened
 * since they last marked that category reviewed. Three categories are tracked: server errors (HTTP 500),
 * other logged errors (everything else -- attacks, protocol violations, client error logs), and new user
 * registrations (account activations). Nothing is built up in advance. Each category carries its own
 * "last reviewed" timestamp, persisted in admin.json under a dedicated admin directory beside the logs.
 *
 * Only sanitized summaries leave this module (see summarizeErrorEntry): a grouping signature per distinct
 * error plus a count. The full, raw context stays in the log files, which the admin is reminded to read.
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

require_once __DIR__ . "/util.php"; // always require_once this file first

// the three review categories, mapped to their admin.json timestamp keys. the client names a category by its
// short key (the array key here) when marking it reviewed, so unknown names are rejected rather than trusted
const ADMIN_REVIEW_CATEGORIES = [
    "serverErrors"  => "serverErrorsReviewedAt",
    "otherErrors"   => "otherErrorsReviewedAt",
    "registrations" => "registrationsReviewedAt"
];

/**
 * the admin state directory: a direct child of the log directory, holding admin.json (and any future
 * intermediate admin state). returns null when no log directory is configured (for example unit tests).
 *
 * @return string|null absolute path to the admin directory, or null when there is no log directory
 */
function adminStateDir() {
    global $autoJaxConfig;
    if ( ! isset($autoJaxConfig->log->error)) {
        return null;
    }
    return dirname($autoJaxConfig->log->error) . "/admin";
}

/**
 * ensure the admin state directory exists, is not world-readable, and carries a deny-all .htaccess so it is
 * never web-served even if the log directory happens to sit inside DOCUMENT_ROOT (the log directory itself is
 * protected the same way). safe to call repeatedly.
 *
 * @return string|null the admin directory path, or null when there is no log directory
 */
function ensureAdminStateDir() {
    $dir = adminStateDir();
    if ($dir === null) {
        return null;
    }
    if ( ! is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    $htaccess = $dir . "/.htaccess";
    if ( ! file_exists($htaccess)) {
        @file_put_contents(
            $htaccess,
            "Options -Indexes\n" .
            "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n" .
            "<IfModule !mod_authz_core.c>\n  Deny from all\n</IfModule>\n"
        );
    }
    return $dir;
}

/**
 * read the admin review state (the per-category "last reviewed" timestamps). a missing file or missing key
 * reads as 0, which means "everything since the beginning of the retained logs".
 *
 * @return array{serverErrorsReviewedAt:int, otherErrorsReviewedAt:int, registrationsReviewedAt:int}
 */
function readAdminState() {
    $dir   = adminStateDir();
    $state = null;
    if ($dir !== null && file_exists($dir . "/admin.json")) {
        $state = json_decode((string)@file_get_contents($dir . "/admin.json"), true);
    }
    if ( ! is_array($state)) {
        $state = [ ];
    }
    $result = [ ];
    foreach (ADMIN_REVIEW_CATEGORIES as $key) {
        $result[$key] = isset($state[$key]) ? (int)$state[$key] : 0;
    }
    return $result;
}

/**
 * mark one category reviewed: set its "last reviewed" timestamp to now, under a lock so concurrent admins
 * cannot clobber each other's other-category timestamps.
 *
 * @param string $category one of the ADMIN_REVIEW_CATEGORIES keys (serverErrors, otherErrors, registrations)
 * @return int the new timestamp written (epoch seconds)
 */
function markAdminReviewed($category) {
    if ( ! isset(ADMIN_REVIEW_CATEGORIES[$category])) {
        dieGracefully(__attack__, "Attack: unknown admin review category: {$category} :: " . json_encode($_SERVER));
    }
    $dir = ensureAdminStateDir();
    if ($dir === null) {
        dieGracefully(SERVER_ERROR, "admin review: no log directory configured, cannot record review");
    }
    $key  = ADMIN_REVIEW_CATEGORIES[$category];
    $now  = time();
    $path = $dir . "/admin.json";
    try {
        $isNew = ! file_exists($path);
        $fh    = fopen($path, "c+");
        if ($fh === false || ! flock($fh, LOCK_EX)) {
            if ($fh !== false) {
                fclose($fh);
            }
            dieGracefully(SERVER_ERROR, "admin review: could not lock admin.json to record review");
        }
        if ($isNew) {
            @chmod($path, 0600);
        }
        $state = json_decode((string)stream_get_contents($fh), true);
        if ( ! is_array($state)) {
            $state = [ ];
        }
        $state[$key] = $now;
        rewind($fh);
        ftruncate($fh, 0);
        fwrite($fh, json_encode($state));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
    }
    catch (\Throwable $thr) {
        dieGracefully(SERVER_ERROR, "admin review: writing admin.json threw: " . $thr->getMessage());
    }
    return $now;
}

/**
 * parse the leading "[d-M-Y H:i:s Timezone]" stamp that reportError and writeLog prefix to every log line,
 * returning its epoch. returns null for a line with no parseable stamp.
 *
 * @param string $line one log line
 * @return int|null epoch seconds, or null when the line has no recognizable timestamp
 */
function parseLogEntryTime($line) {
    if ( ! preg_match('/^\[(\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2}) ([^\]]+)\]/', $line, $m)) {
        return null;
    }
    try {
        $dt = \DateTime::createFromFormat("d-M-Y H:i:s", $m[1], new \DateTimeZone(trim($m[2])));
    }
    catch (\Throwable $thr) {
        return null;
    }
    return $dt === false ? null : $dt->getTimestamp();
}

/**
 * read every line at or after $sinceEpoch from the dated log files matching $globPattern. each entry is a
 * single line (appendToLog collapses embedded CR/LF), so line-by-line parsing is safe. files whose filename
 * date is entirely before the cutoff day are skipped unread, so old logs do not cost anything.
 *
 * @param string $globPattern absolute glob for the dated log files (for example ".../autoJaxError_*.log")
 * @param int    $sinceEpoch  only lines stamped at or after this are returned
 * @return string[] matching log lines, in file then line order
 */
function readLogLinesSince($globPattern, $sinceEpoch) {
    $sinceDay = date("Y_m_d", $sinceEpoch);
    $files    = glob($globPattern);
    if ( ! is_array($files)) {
        return [ ];
    }
    sort($files);
    $lines = [ ];
    foreach ($files as $file) {
        if (preg_match('/_(\d{4}_\d{2}_\d{2})\.log$/', $file, $m) && $m[1] < $sinceDay) {
            continue; // this whole day predates the cutoff -- nothing in it can be in range
        }
        $fh = fopen($file, "r");
        if ($fh === false) {
            continue;
        }
        while (($line = fgets($fh)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === "") {
                continue;
            }
            $time = parseLogEntryTime($line);
            if ($time === null || $time < $sinceEpoch) {
                continue;
            }
            $lines[] = $line;
        }
        fclose($fh);
    }
    return $lines;
}

/**
 * compile the error review for one severity since a cutoff: group the matching error-log entries by their
 * sanitized signature (summarizeErrorEntry) and count each, most frequent first. server errors are HTTP 500.
 * everything else (attacks, protocol violations, client error logs) is "other".
 *
 * @param int  $sinceEpoch only errors stamped at or after this are counted
 * @param bool $serverOnly true for the server-error category, false for the other-error category
 * @return array{total:int, groups:array<int,array{summary:string, count:int}>}
 */
function compileErrorReview($sinceEpoch, $serverOnly) {
    global $autoJaxConfig;
    if ( ! isset($autoJaxConfig->log->error)) {
        return [ "total" => 0, "groups" => [ ] ];
    }
    $globPattern = dirname($autoJaxConfig->log->error) . "/" . datedLogPrefix($autoJaxConfig->log->error) . "*.log";
    $lines       = readLogLinesSince($globPattern, $sinceEpoch);
    $counts      = [ ];
    $total       = 0;
    foreach ($lines as $line) {
        $code     = preg_match('/\bcode:\s*(\d+)/', $line, $m) ? (int)$m[1] : 0;
        $isServer = $code === SERVER_ERROR;
        if ($serverOnly !== $isServer) {
            continue;
        }
        $sig = summarizeErrorEntry($line);
        if ( ! isset($counts[$sig])) {
            $counts[$sig] = 0;
        }
        ++$counts[$sig];
        ++$total;
    }
    arsort($counts);
    $groups = [ ];
    foreach ($counts as $sig => $n) {
        $groups[] = [ "summary" => $sig, "count" => $n ];
    }
    return [ "total" => $total, "groups" => $groups ];
}

/**
 * count new user registrations since a cutoff: the "account activated" events written to the authEvent log
 * when a pending self-registration confirms its email. counts confirmed users, not abandoned pending attempts.
 *
 * @param int $sinceEpoch only activations stamped at or after this are counted
 * @return int the number of activations in range
 */
function countRegistrationsSince($sinceEpoch) {
    global $autoJaxConfig;
    if ( ! isset($autoJaxConfig->log->authEvent)) {
        return 0;
    }
    $globPattern = dirname($autoJaxConfig->log->authEvent) . "/" . datedLogPrefix($autoJaxConfig->log->authEvent) . "*.log";
    $lines       = readLogLinesSince($globPattern, $sinceEpoch);
    $count       = 0;
    foreach ($lines as $line) {
        if (strpos($line, "account activated for uid:") !== false) {
            ++$count;
        }
    }
    return $count;
}

/**
 * derive the dated-log filename prefix from a configured log path, for building the sibling glob. the log
 * paths end in "_YYYY_MM_DD.log", so the prefix is everything up to and including the trailing underscore.
 * "autoJaxError_2026_06_24.log" yields "autoJaxError_".
 *
 * @param string $logPath a configured log file path
 * @return string the filename prefix, including the trailing underscore
 */
function datedLogPrefix($logPath) {
    $base = basename($logPath);
    return preg_replace('/\d{4}_\d{2}_\d{2}\.log$/', "", $base);
}

/**
 * compile the full admin review: each category since its own "last reviewed" timestamp. this is the read the
 * admin page issues on load. only sanitized summaries and counts are returned -- detail stays in the logs.
 *
 * @return array the three category summaries, each carrying the "since" timestamp it was compiled from
 */
function compileAdminReview() {
    $state = readAdminState();
    $server = compileErrorReview($state["serverErrorsReviewedAt"], true);
    $other  = compileErrorReview($state["otherErrorsReviewedAt"], false);
    return [
        "serverErrors"  => [
            "since"  => $state["serverErrorsReviewedAt"],
            "total"  => $server["total"],
            "groups" => $server["groups"]
        ],
        "otherErrors"   => [
            "since"  => $state["otherErrorsReviewedAt"],
            "total"  => $other["total"],
            "groups" => $other["groups"]
        ],
        "registrations" => [
            "since" => $state["registrationsReviewedAt"],
            "count" => countRegistrationsSince($state["registrationsReviewedAt"])
        ]
    ];
}
