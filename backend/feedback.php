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
 * @file feedback.php
 *
 * captrieve-app posts a user's free-text note over HTTPS to autojax/ajaxApi/ajaxPort.php, which routes the
 * call here (see the ajaxApi entry in autojax.json). the message shape is fixed by the app's transport
 * (lib/connected.dart): param = { uuid, text }, where uuid is the install's self-generated id and text is the
 * note. the end result is a log line on the server.
 *
 * the service is public and auth-agnostic: a signed-in caller is neither required nor treated differently, because feedback
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

const feedbackTextMaxLength = 5000; // characters of free-text feedback accepted in one submission
const uuidV4Pattern         = "/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i";

/**
 * required entry point
 *
 * @param object $autoJaxRequest {
 *     @property string $action     Must be "create"
 *     @property string $subService Must be null or omitted
 *     @property object $param {
 *         @property string $uuid The install's self-generated v4 UUID (lib/connected.dart)
 *         @property string $text The free-text feedback note (non-empty, <= feedbackTextMaxLength chars)
 *     }
 * }
 * @return void Exits via ajaxExit() or dieGracefully()
 */
function serviceRequest() {
    global $autoJaxConfig, $autoJaxRequest;
    if ($autoJaxRequest->subService || $autoJaxRequest->action !== "create") {
        dieGracefully(__attack__, "Attack: Invalid feedback request :: " . json_encode($_SERVER));
    }
    $param = $autoJaxRequest->param; // for convenience
    if ( ! isset($param->uuid, $param->text) || ! is_string($param->uuid) || ! is_string($param->text)) {
        dieGracefully(__attack__, "Attack: feedback request has missing or invalid uuid or text :: " . json_encode($_SERVER));
    }
    $uuid = normalizeWhitespace($param->uuid);
    if ( ! preg_match(uuidV4Pattern, $uuid)) {
        dieGracefully(__attack__, "Attack: feedback request has malformed uuid :: " . json_encode($_SERVER));
    }
    // keep the note's own line breaks (endsOnly trim) -- only the ends are stripped, internal newlines stay
    $text = normalizeWhitespace($param->text, true);
    if ($text === "") {
        // a legit client disables submit on an empty note, so an empty one arriving is a protocol violation
        dieGracefully(__attack__, "Attack: empty feedback text :: " . json_encode($_SERVER));
    }
    if (mb_strlen($text) > feedbackTextMaxLength) {
        // possibly legit (a very long note), so tell the app rather than treating it as an attack -- it can
        // surface the limit to the user. UNPROCESSABLE carries the reason payload (see ajaxResponse.php)
        ajaxExit(UNPROCESSABLE, [ "reason" => "feedback text too long" ]);
    }
    $record = json_encode([
        "time" => date("c"),
        "uuid" => $uuid,
        "text" => $text
    ]);
    if ($record === false) {
        dieGracefully(SERVER_ERROR, "feedback: could not encode record :: " . json_encode($autoJaxRequest));
    }
    // one JSON line per submission, in the autojax log directory (web-denied and chmod'd by ajaxPort). the
    // path is derived from the configured error log so this service needs no log config of its own, the same
    // way sendEmail derives its test-mail spool path. appendToLog locks, collapses any CR/LF, and 0600s a new file
    $path = dirname($autoJaxConfig->log->error) . "/captrieveFeedback_" . date("Y_m_d") . ".log";
    if ( ! appendToLog($path, $record)) {
        dieGracefully(SERVER_ERROR, "feedback: could not write feedback log :: {$path}");
    }
    ajaxExit(NO_CONTENT);
}
