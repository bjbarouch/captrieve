<?php

namespace AutoJax {


/**
 * @file clientLog.php
 * receives client-side log messages and writes them to the appropriate server-side log
 *
 * for error entries (logName "error"), routes through reportError() as before
 * for non-error entries, routes through writeLog() to the named log file
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
require_once __DIR__ . "/../util/security.php";

function serviceRequest() {
    global $autoJaxConfig, $autoJaxRequest;
    $jwt = (object)[ "autoJaxId" => 0, "iat" => 0, "exp" => 0 ];
    if ( ! isset($autoJaxRequest->action, $autoJaxRequest->param, $autoJaxRequest->param->message, $autoJaxRequest->param->logName) ||
         $autoJaxRequest->action !== "create"
    ) {
        dieGracefully(__attack__, "Invalid clientLog request :: " . json_encode($_SERVER));
    }
    $logName = $autoJaxRequest->param->logName;
    if ( ! is_string($logName) || ! isset($autoJaxConfig->log->$logName)) {
        dieGracefully(__attack__, "Invalid clientLog logName: {$logName} :: " . json_encode($_SERVER));
    }
    if (isset($autoJaxRequest->jwt) && is_string($autoJaxRequest->jwt)) {
        try {
            $j = decodeJwtWithOverlap($autoJaxRequest->jwt, true, "session");
            // scope to "session": a reset or confirm token has no business arriving on the clientLog path, so
            // it must not populate uid context. only a successful full decode yields the payload object. an
            // expired token returns the string "expired", and a wrong type or other soft failure throws or
            // returns null -- in any of those cases keep the zeroed jwt set above. this stays best-effort log
            // context only, never an auth decision, so a non-session token is logged with a zeroed uid rather
            // than rejected (clientLog must accept a report even from a just-ended session)
            if (is_object($j)) {
                $jwt = $j;
            }
        }
        catch (\Throwable $thr) {
            // invalid jwt -- use the zeroed-out jwt set above
        }
    }
    $message = "jwt = { uid: " . $jwt->autoJaxId .
               ", iat: " . date("Y-m-d H:i:s", $jwt->iat) .
               ", exp: " . date("Y-m-d H:i:s", $jwt->exp) .
               " }, " . $autoJaxRequest->param->message;
    if ($logName === "error") {
        reportError(__client_error__, $message);
    }
    else {
        writeLog($logName, $message);
    }
    ajaxExit(NO_CONTENT);
}

} // end namespace AutoJax

