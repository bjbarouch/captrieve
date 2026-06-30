<?php

namespace AutoJax;

// always require_once this file first, unless you require_once autojax-secrets.php first and it requires this

// circular dependency trick
$util = "util";
if ( ! isset($autoJaxConfig)) {
    // if config file contains secret information (which is the usual case -- email and db credentials, encryption keys and
    // secrets), put it outside of DOCUMENT_ROOT so it cannot possibly be displayed as plain text over http(s), and adjust
    // this require_once to the correct path
    // config already loaded by host wrapper
}

/**
 * @file util.php
 *
 * misc utility routines
 *
 * - error and exception handling
 *   - Converts all PHP errors into ErrorException via set_error_handler()
 *   - Registers a global set_exception_handler() that logs and fails gracefully
 *   - reportError() for consistent error logging to a dedicated file
 *   - Conditional escalation via email to tech support on serious conditions
 *   - dieGracefully() helper to log, notify, and respond with a JSON error
 * - HTTP status code policy
 *   - Defines the small set of HTTP codes typically used in ajax exchanges
 *   - Documents which codes may/must/cannot include data
 *   - Clarifies the semantics of __unauthorized__, __unauthenticated__, __attack__, and SERVER_ERROR
 * - data and encoding helpers:
 *   - base64url_encode()/base64url_decode() for JWT-safe Base64 variants
 *   - classifyDataString() to distinguish hex/base64/base64url/text/binary input
 *   - jsonDecode() to decode json with error logging
 * - file and path utilities
 *   - sanitizeFilename()/imageFileName() for secure handling of uploaded filenames
 *   - makeDirectory() as a robust mkdir() wrapper with error reporting
 *   - saveFile() to write files, creating missing directories and applying sane permissions
 *
 * - uses ob_start() defensively to avoid intermittent observed but inexplicable "headers already sent" issues
 * - hides internal error details from end users while logging fully
 *
 * - required at the top of most other files
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

// it makes no sense to me, but in sendEmail(), which sends nothing to the browser, there is a
// line that is *intermittently* identified as the source of the error of sending headers after
// having sent data -- intermittent, nonsensical, so luckily we can just do this
ob_start();
// we don't bother with ob_end_flush() and ob_clean() because ob_end_flush happens automatically
// on exit, and ob_clean becomes immaterial on exit

/**
 * PHP does not complain loudly enough about problems it encounters
 * we'd rather call out all issues and clean up our code
 *
 * Respects the current error_reporting mask and the @ suppression operator: an error that the
 * configured error_reporting excludes (or that a caller explicitly suppressed with @) is left for
 * PHP's default handling rather than escalated to an exception. This is the standard, recommended
 * pattern -- it stops third-party code that legitimately suppresses a warning (for example PHPUnit's
 * process-isolation template under newer PHP) from being turned into a fatal in our process.
 *
 * @param int    $severity Error severity level
 * @param string $message  Error message
 * @param string $file     File in which the error occurred
 * @param int    $line     Line number of the error
 * @return bool
 * @throws ErrorException
 */
set_error_handler(function($severity, $message, $file, $line) {
    if ( ! (error_reporting() & $severity)) {
        return false; // suppressed by @ or excluded by error_reporting -- do not escalate
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

/**
 * global exception handler that logs uncaught errors
 *
 * @param Throwable|mixed $thr The error object or message
 */
set_exception_handler(function ($thr) {
    if ($thr instanceof \Throwable) {
        dieGracefully(
            SERVER_ERROR,
            "Uncaught throwable: " . $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    else {
        dieGracefully(SERVER_ERROR, "Uncaught non-throwable error");
    }
});

ini_set("display_errors", "0"); // don't show errors to the user
ini_set("html_errors", "0");    // don't show errors to the user
error_reporting(E_ALL);         // catch all errors, warnings, notices, whatever

function debugger() { // insert into code to stop if and only if running within the debugger
    if (function_exists("xdebug_break")) {
        xdebug_break();
    }
}

/**
 * detect the current PHP runtime context
 *
 * - CLI (command-line, cron, workers, tests)
 * - Web (Apache, nginx + PHP-FPM, etc.)
 *
 * @return string One of: "cli", "web", or "unknown"
 */
function detectRuntime() {
    if (PHP_SAPI === "cli" || PHP_SAPI === "phpdbg") {
        return "cli";
    }
    if (isset($_SERVER["REQUEST_METHOD"])) {
        return "web";
    }
    return "unknown";
}

/**
 * True if running in a CLI context.
 *
 * @return bool
 */
function inCli() {
    return detectRuntime() === "cli";
}

/**
 * True if running in a web (HTTP) context.
 *
 * @return bool
 */
function inWeb() {
    return detectRuntime() === "web";
}

/**
 * Detect whether the current environment is development and test or production.
 *
 * Reads the DEV_AND_TEST environment variable. On a controlled dev or test machine the
 * variable is exported in the shell. For a deployed build it is stamped in by build.js:
 * a dev/test build sets it to "true", a production build sets it to nothing
 * (see dist/autojax-buildenv.php, loaded by ajaxPort.php).
 *
 * Production is the safe default: any value other than "true" -- absent, empty, or
 * "false" -- is treated as production.
 *
 * An automated test run always implies dev and test, regardless of the build stamp. The harness
 * signals itself out of band (see inAutomatedTesting), so a production build that is later driven
 * by the test suite is still routed at the test database, never the production one. This closes the
 * gap where a production --reinstall would reset the build stamp and silently aim e2e at production.
 *
 * @return bool true if in dev and test
 */
function inDevAndTest() {
    if (inAutomatedTesting()) {
        return true;
    }
    return strcasecmp((string)getenv("DEV_AND_TEST"), "true") === 0;
}

/**
 * True when running under an automated test harness. This is the single switch for accommodations that only
 * make sense for unattended automated tests -- for example, spooling outbound email to a file instead of
 * sending it, because the test machine has no mail server. Kept separate from DEV_AND_TEST on purpose: a
 * human doing dev or test work is not the same as an automated run, and config the human sees should not
 * mention it. The harness sets this only for the duration of a run, never persistently.
 *
 * Two equivalent signals, so any harness can set it ephemerally at the start of a run:
 *   - the AUTOJAX_AUTOMATED_TESTING environment variable is "true" (for an in-process or server-launched
 *     harness, e.g. the PHPUnit bootstrap), or
 *   - a ".autojaxAutomatedTesting" marker file sits in autojax's parent directory (for a cross-process
 *     harness like Playwright, whose Node runner cannot set the separate PHP server's environment).
 *
 * @return bool true when either signal is present
 */
function inAutomatedTesting() {
    if (strcasecmp((string)getenv("AUTOJAX_AUTOMATED_TESTING"), "true") === 0) {
        return true;
    }
    return file_exists(dirname(__DIR__, 2) . "/.autojaxAutomatedTesting");
}

/**
 * True if running in production.
 *
 * Production is the default unless DEV_AND_TEST is "true".
 *
 * @return bool
 */
function inProd() {
    return ! inDevAndTest();
}

/**
 * Detect a local mail-submission binary -- the "sendmail" interface that postfix, exim, and sendmail all
 * provide, and the thing the default "sendmail" email transport hands messages to. Returns the path to the
 * first usable binary, or null when none is found.
 *
 * A found binary is necessary but not proof of deliverability: the host may still refuse to relay. The only
 * certain test is an actual send. install.sh uses this to warn loudly when nothing is present, which is the
 * common, fixable misconfiguration.
 *
 * @return string|null absolute path to a sendmail-compatible binary, or null when none is usable
 */
function detectLocalMta() {
    $configured = (string)ini_get("sendmail_path");
    $candidate  = $configured !== "" ? strtok($configured, " ") : "";
    $paths      = array_filter([ $candidate, "/usr/sbin/sendmail", "/usr/lib/sendmail", "/usr/bin/sendmail" ]);
    foreach ($paths as $path) {
        if (@is_executable($path)) {
            return $path;
        }
    }
    return null;
}

// stupid common practices we have to accommodate:
//     UNAUTHORIZED is universally misappropriated to mean unauthenticated
//     FORBIDDEN is universally used to mean authenticated but unauthorized
// Grr
// the ugliness we commit in the context of limited and inflexible http codes, is as follows, to
// make code self-documenting

// those few HTTP status codes we encounter in our work:
const OKAY                 = 200;
const NO_CONTENT           = 204;
const UNAUTHORIZED         = 401;
const __unauthenticated__  = 401; // to preserve my sanity and make code self-documenting
const FORBIDDEN            = 403;
const __unauthorized__     = 403; // to preserve my sanity and make code self-documenting
const __attack__           = 403; // to preserve my sanity and make code self-documenting
const NOT_FOUND            = 404;
const REQUEST_TIMEOUT      = 408;
const CONFLICT             = 409; // possibly transient state prevents success (such as current resource consumption)
const CONTENT_TOO_LARGE    = 413;
const __client_error__     = 418; // incoming client error log entry (traditionally, "I'm a teapot")
const UNPROCESSABLE        = 422; // well-formed but invalid – would violate business rules
const UPGRADE_REQUIRED     = 426;
const TOO_MANY_REQUESTS    = 429;
const SERVER_ERROR         = 500;
const BAD_GATEWAY          = 502;
const SERVICE_UNAVAILABLE  = 503;
const GATEWAY_TIMEOUT      = 504;

function dataRequired($code) {
    return  $code === OKAY ||           // responsive payload
            $code === __client_error__; // error message from client to be logged by server
}

function dataAllowed($code) {
    return  $code === __unauthenticated__ ||
            $code === __unauthorized__    ||
            $code === CONFLICT            ||
            $code === TOO_MANY_REQUESTS   ||
            $code === UNPROCESSABLE       ||
            // the auto-retry codes: a bare response retries, a response carrying a payload is a client message
            $code === REQUEST_TIMEOUT     ||
            $code === BAD_GATEWAY         ||
            $code === SERVICE_UNAVAILABLE ||
            $code === GATEWAY_TIMEOUT;
}
// 201 CREATED allows data, but we do not use it
// the auto-retry codes (408, 502, 503, 504) carry data only as a deliberate signal. a bare one is a transient
// outage the client retries. one carrying a payload is a finished message for the client (for example, the
// mail system refused a registration), which the client surfaces instead of retrying. "carries data" means a
// parsed json payload, so proxy-generated html error pages -- which are not json -- stay in the retry path.
// this bifurcation is documented in the autojax README. see ajaxPost.js for the client half of the rule

// jwt-only-and-optional: carries no business data, but for an authenticated caller a refreshed jwt may
// ride along (the only permissible payload). an unauthenticated caller gets the bare code. distinct from
// dataAllowed (which permits business data) and from dataProhibited (which permits nothing). only
// CONTENT_TOO_LARGE qualifies: NO_CONTENT cannot, because HTTP forbids a body on a 204 (session refresh
// after a no-content result is handled by keepAlive returning 200 instead) -- so 204 stays dataProhibited.
function jwtOnlyAndOptional($code) {
    return $code === CONTENT_TOO_LARGE;
}

// every http code not explicitly mentioned above is in the dataProhibited group
function dataProhibited($code) {
    return ! dataRequired($code) && ! dataAllowed($code) && ! jwtOnlyAndOptional($code);
}

// the single, positive decision for whether a response may carry a refreshed session jwt. a refresh
// extends the session, so it may ride along only on a code that means the session is still alive. this
// is deliberately the one place that decision is made -- ajaxExit gates every jwt attachment on it -- so
// the rule cannot drift across the several branches that build a response. __unauthenticated__ (401) is
// excluded outright: it means there is no live session, so refreshing one in the same response is a
// contradiction and would defeat session revocation (a revoked-session 401 must not hand back a usable
// token). NO_CONTENT is excluded because HTTP forbids a 204 body (the keepAlive 200-trick handles
// refresh-with-no-content), and NOT_FOUND by policy. everything that represents a live session -- OKAY,
// the jwt-only CONTENT_TOO_LARGE, and the dataAllowed business codes (403/409/422/429) -- may refresh.
function responseCarriesSessionJwt($code) {
    if ($code === __unauthenticated__) {
        return false;
    }
    return $code === OKAY || $code === CONTENT_TOO_LARGE || dataAllowed($code);
}

/**
 * return the authenticated user's ID for the current request
 * reads from $autoJaxRequest->user->autoJaxId when a full session is established (normal authenticated requests)
 * falls back to $autoJaxRequest->autoJaxId for the login response path, where user hydration has not run
 *
 * @return int  user ID, or 0 for unauthenticated
 */
function getUserId() {
    global $autoJaxRequest;
    if (isset($autoJaxRequest->user) && is_object($autoJaxRequest->user) && isset($autoJaxRequest->user->autoJaxId)) {
        return $autoJaxRequest->user->autoJaxId;
    }
    return isset($autoJaxRequest->autoJaxId) && $autoJaxRequest->autoJaxId
        ? $autoJaxRequest->autoJaxId
        : 0;
}

/**
 * appends a pre-formatted entry to a log file with concurrency-safe locking.
 *
 * embedded CR and LF characters are collapsed to spaces so that attacker-controlled content
 * (for example a client-supplied log message) cannot forge extra log lines (log injection).
 * the single trailing newline that separates entries is added here. a newly created log file is
 * chmod'd to 0600 so log contents are not left world-readable.
 *
 * @param string $path  absolute path to the log file
 * @param string $entry the log line, without trailing newline
 * @return bool  true on success, false on failure
 */
function appendToLog($path, $entry) {
    $isNew = ! file_exists($path);
    $entry = str_replace([ "\r", "\n" ], " ", $entry);
    $ok    = file_put_contents($path, "{$entry}\n", FILE_APPEND | LOCK_EX);
    if ($ok !== false && $isNew) {
        @chmod($path, 0600);
    }
    return $ok !== false;
}

/**
 * Automated test only: spool an email-delivered token to a file the test harness can read directly.
 *
 * Account confirmation and password reset both hand the user a single-use token by email. An unattended
 * run (e.g. Playwright) has no inbox, so it cannot click the link. The whole email is already spooled to
 * autoJaxTestMail.log by spoolTestEmail(), but the token sits inside the rendered body, so a test would
 * have to scrape a URL out of prose. This writes the same token as a structured record instead: one JSON
 * line per token, holding its type, the recipient's id and address, the raw token, and the full link.
 *
 * Self-gated on inAutomatedTesting(): outside an automated run this returns immediately, so the call sites
 * need no guard and a token can never reach disk in production. That is the same single boundary that
 * already governs spoolTestEmail() -- the email body carrying this token is only written under that gate --
 * so this adds no production exposure, only a cleaner shape for the harness to consume.
 *
 * @param string $type      the token type, e.g. "email_confirm" or "password_reset"
 * @param int    $autoJaxId the recipient's autoJaxUserId
 * @param string $email     the recipient email address
 * @param string $token     the raw JWT exactly as it appears in the email link
 * @param string $url       the full link delivered to the user, token included
 * @return void
 */
function captureTestToken($type, $autoJaxId, $email, $token, $url) {
    global $autoJaxConfig;
    if ( ! inAutomatedTesting()) {
        return; // never write a token to disk outside an automated test run
    }
    if ( ! isset($autoJaxConfig->log->error)) {
        return; // no log directory is configured, so there is nowhere safe to write the spool
    }
    $record = json_encode([
        "time"      => date("c"),
        "type"      => $type,
        "autoJaxId" => $autoJaxId,
        "email"     => $email,
        "token"     => $token,
        "url"       => $url
    ]);
    if ($record === false) {
        return; // a record that will not json-encode is not worth disturbing a real flow over
    }
    appendToLog(dirname($autoJaxConfig->log->error) . "/autoJaxTestTokens.log", $record);
}

/**
 * writes error messages to a dedicated error log file
 *
 * @param int    $errCode  HTTP status code representing the error
 * @param string $message  human-readable error message
 * @return void
 */
function reportError($errCode = SERVER_ERROR, $message = "no message! (bad programmer!)") {
    global $autoJaxConfig;
    $now = date("d-M-Y H:i:s ") . date_default_timezone_get(); // php error_log format, except for using local time zone
    if (gettype($errCode) === "string") { // devs might forget to set the code, and just pass the message
        $message = $errCode;
        $errCode = SERVER_ERROR; // assume the worst, and hopefully prompt dev to fix invocation
    }
    switch ($errCode) {
        case __attack__ :
            $message = "ATTACK LIKELY, but possibly a programming error in client or server code: {$message} :: " .
                json_encode($_SERVER);
            break;
        case SERVER_ERROR :
            $message = "SERVER ERROR, SITE MIGHT BE DOWN -- {$message}";
            break;
        default :
            // no special preamble added
    }
    $autoJaxId = "autoJaxId: " . getUserId();
    $logEntry = "code: {$errCode} {$autoJaxId} message: {$message}";
    if (isset($autoJaxConfig->log->error)) {
        try {
            if ( ! appendToLog($autoJaxConfig->log->error, "[{$now}] {$logEntry}")) {
                $err = error_get_last();
                error_log("could not write to application-specific error log: " . ($err["message"] ?? "unknown"));
                error_log("error log message that could not be written: {$logEntry}");
            }
        }
        catch (\Throwable $thr) {
            error_log("writing to application-specific error log threw: {$thr->getMessage()} :: {$logEntry}");
        }
    }
    else {
        if ( ! defined("TESTING_MODE")) { // suppress during unit tests -- output has nowhere useful to go
            error_log($logEntry); // automatically inserts its own timestamp in the same format as we mimicked above (but UTC)
        }
    }
}

/**
 * condense one error-log entry into a short, safe one-liner: the error code plus the start of its message,
 * stripped of the long fixed preambles, restricted to printable ASCII (no control chars), and truncated. used
 * as the grouping signature and the display string for the admin error review, so the same recurring error
 * collapses to one line and nothing attacker-influenceable or binary reaches the page. the raw entry, with its
 * full context, stays in the log file.
 *
 * @param string $entry one error-log line
 * @return string condensed summary, for example "500 db.php connect failed"
 */
function summarizeErrorEntry($entry) {
    $code    = preg_match('/\bcode:\s*(\S+)/', $entry, $m) ? $m[1] : "";
    $pos     = strpos($entry, "message:");
    $message = $pos === false ? $entry : substr($entry, $pos + strlen("message:"));
    $message = preg_replace('/[^\x20-\x7E]/', " ", $message); // printable ASCII only -- no control chars, no MIME
    $message = trim(preg_replace('/\s+/', " ", $message));
    $message = preg_replace('/^SERVER ERROR, SITE MIGHT BE DOWN -- /', "", $message);
    $message = preg_replace('/^ATTACK LIKELY[^:]*:\s*/', "", $message);
    if (strlen($message) > 60) {
        $message = rtrim(substr($message, 0, 60)) . "...";
    }
    return trim("{$code} {$message}");
}

/**
 * Exception thrown in testing mode to make dieGracefully() behavior testable.
 */
class DieGracefullyException extends \Exception {
    public $httpCode;

    public function __construct($httpCode, $message) {
        $this->httpCode = $httpCode;
        parent::__construct($message, (int)$httpCode);
    }
}

/**
 * logs an error via reportError(), then sends an error response via ajaxExit().
 *
 * @param int    $code    HTTP status code to return
 * @param string $message Error message for the log
 * @return void
 */
function dieGracefully($code = SERVER_ERROR, $message = "no message! (bad programmer!)") {
    if (is_string($code)) { // called with message and no code!
        $message = $code;
        $code    = SERVER_ERROR;
    }
    reportError($code, $message);
    ajaxExit($code);
}

/**
 * writes a message to the log file configured for $logId, with file locking for concurrent-request safety.
 * unrecognized log ids are fallen back to PHP's error_log with a clear marker.
 *
 * @param string $logId   application-defined log identifier, must exist in $autoJaxConfig->log
 * @param string $message human-readable message
 * @return void
 */
function writeLog($logId, $message) {
    global $autoJaxConfig;
    $now = date("d-M-Y H:i:s ") . date_default_timezone_get(); // php error_log format, except for using local time zone
    $logEntry = "user id: " . getUserId() . " message: {$message}";
    if (isset($autoJaxConfig->log) && isset($autoJaxConfig->log->$logId)) {
        try {
            if ( ! appendToLog($autoJaxConfig->log->$logId, "[{$now}] {$logEntry}")) {
                $err = error_get_last();
                reportError(
                    SERVER_ERROR,
                    "could not write to log {$logId}: " . ($err["message"] ?? "unknown") . " :: {$logEntry}"
                );
            }
        }
        catch (\Throwable $thr) {
            reportError(SERVER_ERROR, "writing to log {$logId} threw: {$thr->getMessage()} :: {$logEntry}");
        }
    }
    else {
        reportError(SERVER_ERROR, "UNRECOGNIZED LOG ID: {$logId} -- {$logEntry}");
    }
}

// includes unicode characters
function normalizeWhitespace($str, $endsOnly = false) {
    $str = preg_replace('/^\s+|\s+$/u', "", $str); // like trim(), but includes unicode
    return $endsOnly
            ? $str                               // like trim()
            : preg_replace('/\s+/u', " ", $str); // reduce runs of ASCII and unicode whitespace to a single ASCII space
}

/**
 * if string, trim (by reference). then, if string and === "" it's true, else false
 */
function isBlankString(&$str) {
    if (is_string($str)) {
        $str = normalizeWhitespace($str);
    }
    return is_string($str) && $str === "";
}

/**
 * encodes data in base64 URL-safe format (RFC 7515).
 *
 * @param string $data binary data to encode.
 * @return string base64URL-encoded string without padding.
 */
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

/**
 * decodes base64 URL-safe data.
 *
 * @param string $data base64URL-encoded string.
 * @return string|false decoded binary data, or false on failure.
 */
function base64url_decode($data) {
    $data = strtr($data, "-_", "+/");
    $padding = strlen($data) % 4;
    if ($padding) {
        $data .= str_repeat("=", 4 - $padding);
    }
    $data = base64_decode($data, true);
    if ($data === false) {
        throw new \Exception("cannot decode alleged but invalid base64 data");
    }
    if ($data === "") {
        throw new \Exception("cannot decode empty base64 data");
    }
    return $data;
}

/**
 * classify a string as one of: "hex", "base64", "base64url", "text", or "binary".
 *
 * "hex":       A valid even-length hexadecimal string (0-9, a-f, A-F).
 * "base64":    A valid Base64-encoded string (A-Z, a-z, 0-9, +, /, =).
 * "base64url": A valid Base64-encoded string (A-Z, a-z, 0-9, -, _, =).
 * "text":      Printable UTF-8 string (includes whitespace and control chars like \n).
 * "binary":    Everything else (likely raw or non-printable binary data).
 *
 * @param string $input The string to classify.
 *
 * @return string One of "hex", "base64", "base64url", "text", or "binary".
 */
function classifyDataString($input) {
    // hex: even length, all hex digits
    if (strlen($input) % 2 === 0 && ctype_xdigit($input)) {
        return "hex";
    }
    // base64: must match valid characters, decode successfully, and re-encode to same
    if (preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $input)) {
        $decoded = base64_decode($input, true);
        if ($decoded !== false) {
            if (base64_encode($decoded) === $input) {
                return "base64";
            }
        }
    }
    // base64url: same decoding rules, but with - and _ instead of + and /
    if (preg_match('/^[A-Za-z0-9\-_]+={0,2}$/', $input)) {
        $base64 = strtr($input, "-_", "+/");
        $padded = $base64 . str_repeat("=", (4 - strlen($base64) % 4) % 4);

        $decoded = base64_decode($padded, true);
        if ($decoded !== false) {
            if (base64_encode($decoded) === $padded) {
                return "base64url";
            }
        }
    }
    // text
    if (preg_match('/^[\x20-\x7E\r\n\t]+$/', $input)) {
        return "text";
    }
    // otherwise, assume binary
    return "binary";
}

function jsonDecode($json) {
    if ( ! is_string($json)) {
        throw new \Exception("json must be a string");
    }
    try {
        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
    catch (\JsonException $exc) {
        reportError(SERVER_ERROR, "Invalid json: " . $exc->getMessage() . " :: " . $json);
        throw $exc; // so caller can give more context in a separate error message
    }
}

/**
 * sanitize a filename to remove dangerous characters and patterns.
 *
 * @param string $filename The original filename
 * @return string          A safe, sanitized filename
 */
function sanitizeFilename($filename) {
    // remove any path components
    $filename = basename($filename);
    // replace unsafe characters with underscores
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
    // no "dot files"
    if (strpos($filename, '.') === 0) {
        $filename = 'file' . $filename;
    }
    return $filename;
}

/**
 * sanitizes a filename and checks ext against a list of supported image file types
 *
 * @param string $filename The original filename
 * @return string|false          A safe, sanitized, legit filename, or false
 */
function imageFileName($filename) {
    $name = sanitizeFilename($filename);
    $ext  = strrchr($name, '.');
    if ( ! $ext) {
        return false; // not a supported image file name
    }
    return in_array($ext, [ ".png", ".jpg", ".jpeg", ".gif", ".webp", ".avif", ".bmp", ".heic" ])
        ? $name : false;
}

/**
 * verify that an uploaded file's actual content matches an allowed image MIME type, sniffed from the
 * file's bytes rather than trusted from the filename or the client-supplied Content-Type.
 *
 * All listed types, including SVG, are accepted and stored byte-for-byte unchanged -- an archive must hand
 * back exactly what it was given, so nothing here mutates or sanitizes the upload. An SVG is XML that can
 * carry embedded script, but that script runs only when a browser renders the file as a top-level document.
 * The safety is therefore in how the file is SERVED, not in rejecting it at upload: display user SVG only as
 * an image (an <img> or CSS background renders SVG in the browser's secure static mode -- no script, no
 * external loads, no interactivity), never as a navigable document, an <object>/<embed>/<iframe>, or DOM
 * injected markup. AutoJax's own download path enforces this by serving every file as an attachment with
 * X-Content-Type-Options: nosniff and Content-Security-Policy: script-src 'none' (see ajaxResponse.php), so a
 * navigation downloads rather than renders. See docs/security-posture.md section 11 for the full rule that a
 * consumer must follow when serving SVG inline for preview.
 *
 * @param string $tmpFile  the tmp_name from $_FILES
 * @return bool            true if the content is an allowed image type
 */
function isUploadedImageSafe($tmpFile) {
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpFile);
    $allowed = [
        "image/png",
        "image/jpeg",
        "image/gif",
        "image/webp",
        "image/avif",
        "image/bmp",
        "image/heic",
        "image/svg+xml"
    ];
    return in_array($mimeType, $allowed, true);
}

// wrap mkdir to make it more robust
function makeDirectory($dir) {
    if (is_dir($dir)) {
        return; // given directory already exists
    }
    // mkdir occasionally returns false even on success – is_dir() below catches that case
    // sometimes mkdir returns false even though it has worked! so follow with is_dir
    // note: with third argument === true, mkdir handles all intermediate dirs, if any, to reach the full path given
    try {
        if ( ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            dieGracefully(SERVER_ERROR, "Could not create directory: {$dir}");
        }
    }
    catch (\ErrorException $e) {
        dieGracefully(SERVER_ERROR, "Could not create directory: {$dir} :: " . $e->getMessage());
    }
}

/**
 * save a file to the given path, creating any missing directories
 *
 * @param string $path     full path to the file, e.g. "./images/123/456/cover.jpg"
 * @param string $contents file content (binary string)
 * @return bool            true on success, false on failure
 */
function saveFile($path, $contents) {
    makeDirectory(dirname($path));
    if (file_put_contents($path, $contents) === false) {
        return false;
    }
    chmod($path, 0644);
    return true;
}

/**
 * strip // line comments and /* block comments from a JSON5 string
 * does not modify content inside quoted strings
 *
 * @param  string $src  raw JSON5 text
 * @return string       JSON-safe text
 */
function stripJson5Comments($src) {
    $out      = "";
    $len      = strlen($src);
    $inString = false;
    $escape   = false;
    $i        = 0;
    while ($i < $len) {
        $ch = $src[$i];
        if ($escape) {
            $out    .= $ch;
            $escape  = false;
            ++$i;
            continue;
        }
        if ($inString) {
            if ($ch === "\\") {
                $escape = true;
            }
            else {
                if ($ch === "\"") {
                    $inString = false;
                }
            }
            $out .= $ch;
            ++$i;
            continue;
        }
        if ($ch === "\"") {
            $inString = true;
            $out     .= $ch;
            ++$i;
            continue;
        }
        if ($ch === "/" && $i + 1 < $len) {
            $next = $src[$i + 1];
            if ($next === "/") {
                $i += 2;
                while ($i < $len && $src[$i] !== "\n") {
                    ++$i;
                }
                continue;
            }
            if ($next === "*") {
                $i += 2;
                while ($i + 1 < $len && ! ($src[$i] === "*" && $src[$i + 1] === "/")) {
                    ++$i;
                }
                $i += 2;
                continue;
            }
        }
        $out .= $ch;
        ++$i;
    }
    return $out;
}
