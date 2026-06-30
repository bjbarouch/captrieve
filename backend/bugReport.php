<?php

use function AutoJax\normalizeWhitespace;
use function AutoJax\appendToLog;
use function AutoJax\ajaxExit;
use function AutoJax\dieGracefully;
use const AutoJax\__attack__;
use const AutoJax\NO_CONTENT;
use const AutoJax\UNPROCESSABLE;
use const AutoJax\SERVER_ERROR;

/**
 * @file bugReport.php
 *
 * captrieve-app posts a PII-free diagnostic trail over HTTPS to autojax/ajaxApi/ajaxPort.php, which routes
 * the call here (see the ajaxApi entry in autojax.json). the message shape is fixed by the app's transport
 * (lib/connected.dart): param = { uuid, log }, where uuid is the install's self-generated id and log is an
 * array of structured AppLog entries (already scrubbed of user content on the device). the end result is a log
 * line on the server.
 *
 * the service is public and auth-agnostic: a signed-in caller is neither required nor treated differently, because a bug report
 * carries an install id, never an account.
 * ajaxPort's per-IP public throttle bounds abuse.
 * success is 204 No Content, which is what the app's outbox treats as delivered.
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

require_once __DIR__ . "/autojax/util/util.php";

const bugReportMaxEntries = 2000; // log entries accepted in one report (matches the app's on-device cap)
const uuidV4Pattern       = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i";

/**
 * required entry point
 *
 * @param object $autoJaxRequest {
 *     @property string $action     Must be "create"
 *     @property string $subService Must be null or empty
 *     @property object $param {
 *         @property string  $uuid The install's self-generated v4 UUID (lib/connected.dart)
 *         @property array   $log  Structured AppLog entries, newest first (<= bugReportMaxEntries entries)
 *     }
 * }
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function serviceRequest() {
    global $autoJaxConfig, $autoJaxRequest;
    if ($autoJaxRequest->subService || $autoJaxRequest->action !== "create") {
        dieGracefully(__attack__, "Attack: Invalid bugReport request :: " . json_encode($_SERVER));
    }
    $param = $autoJaxRequest->param; // for convenience
    if ( ! isset($param->uuid, $param->log) || ! is_string($param->uuid) || ! is_array($param->log)) {
        dieGracefully(__attack__, "Attack: bugReport request has missing or invalid uuid or log :: " . json_encode($_SERVER));
    }
    $uuid = normalizeWhitespace($param->uuid);
    if ( ! preg_match(uuidV4Pattern, $uuid)) {
        dieGracefully(__attack__, "Attack: bugReport request has malformed install uuid :: " . json_encode($_SERVER));
    }
    if (count($param->log) > bugReportMaxEntries) {
        // possibly legit (a long trail), so tell the app rather than treating it as an attack. UNPROCESSABLE
        // carries the reason payload (see ajaxResponse.php). the app caps its own log, so this rarely fires
        ajaxExit(UNPROCESSABLE, [ "reason" => "bug report too large" ]);
    }
    // re-encode the whole record rather than echoing the raw bytes, so only well-formed JSON reaches the log.
    // the entries are PII-free by the app's design, so there is no user content here to scrub or redact
    $record = json_encode([
        "time" => date("c"),
        "uuid" => $uuid,
        "log"  => $param->log
    ]);
    if ($record === false) {
        dieGracefully(SERVER_ERROR, "bugReport: could not encode record :: " . json_encode($autoJaxRequest));
    }
    // one JSON line per report, in the autojax log directory (web-denied and chmod'd by ajaxPort). the path is
    // derived from the configured error log so this service needs no log config of its own, the same way
    // sendEmail derives its test-mail spool path. appendToLog locks, collapses any CR/LF, and 0600s a new file
    $path = dirname($autoJaxConfig->log->error) . "/captrieveBugReport_" . date("Y_m_d") . ".log";
    if ( ! appendToLog($path, $record)) {
        dieGracefully(SERVER_ERROR, "bugReport: could not write bug-report log :: {$path}");
    }
    ajaxExit(NO_CONTENT);
}
