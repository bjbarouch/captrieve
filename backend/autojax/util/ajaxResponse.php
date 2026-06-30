<?php

namespace AutoJax;

/**
 * @file ajaxResponse.php
 *
 * the terminal response helpers for the ajax pipeline: ajaxExit(), exitUnauthorized(), and the
 * Content-Type helper they use. extracted from ajaxPort.php so they can be loaded and unit-tested in
 * isolation (ajaxPort.php runs the whole request pipeline at include time, which a test cannot do).
 *
 * a service ends the request through exactly one of ajaxExit(), exitUnauthorized(), or dieGracefully().
 * ajaxExit() is the single place a refreshed session jwt is attached to a response: it rides along for an
 * authenticated caller (uid > 0) on any code that permits a body, which means OKAY/dataAllowed codes carry
 * it alongside their business data, and CONTENT_TOO_LARGE carries it alone (the one jwtOnlyAndOptional code).
 * NO_CONTENT never carries a body -- HTTP forbids a 204 body -- so a no-content result that still needs to
 * refresh the session returns OKAY with a keepAlive marker that the client maps back to NO_CONTENT.
 *
 * depends on util.php (getUserId, the data* / jwtOnlyAndOptional classifiers, dieGracefully, constants),
 * security.php (createJwt), and authThrottling.php (resetIpThrottle). ajaxPort.php require_once's all of
 * those before this file.
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
 * Sends a Content-Type header appropriate to the specified type.
 * When returning a code with no data (for NO_CONTENT and most errors), use:
 *    `header_remove("Content-Type"); header("Content-Length: 0");`
 *  -- NOTE: we do this in case PHP does the right thing in a later version
 *  -- at present, when we do this, it sends `Content-Type: text/html; charset=UTF-8` anyway
 * For any responsive data that is not a file download, we use json.
 *
 * @param string $type json or octet
 * @return void
 */
function emitContentTypeHeader($type = "json") {
    switch ($type) {
        case "octet":
            header("Content-Type: application/octet-stream"); // file download or similar
            break;
        case "json":
            header("Content-Type: application/json; charset=UTF-8");
            // we use json for all data other than file uploads and downloads
            // this provides consistent scenarios for both server and client code, and allow us to include and automate
            // handling of a jwt auth token for all calls for authenticated users.
            break;
        /* we don't use any other types
        -- we send all forms of text with application/json, and any type of file with application/octet-stream
            application/javascript
            application/pdf
            application/x-www-form-urlencoded
            application/xml
            audio/mpeg
            audio/ogg
            image/gif
            image/jpeg
            image/png
            image/webp
            multipart/form-data
            multipart/mixed
            multipart/related
            text/css
            text/csv
            text/html
            text/javascript
            text/plain
            text/xml
            video/mp4
            video/webm
        */
        default :
            dieGracefully(SERVER_ERROR, "Server trying to respond with unsupported data type: {$type}");
    }
}

/**
 * Send client a JSON response, including a refreshed jwt, when server-side processing is done.
 * Services should call ajaxExit() to end the program in any happy case, or exitUnauthorized() or dieGracefully() as needed
 *
 * @param int   $code     HTTP status code to send
 * @param array|object|null $data
 *        Response payload:
 *        - If null, no response body is sent (except jwt when applicable).
 *        - If array, treated as JSON response data.
 *        - If object or array contains a "file" element (data->file or data["file"]), triggers file download mode.
 * @return void Exits script after sending json object with data response (if any) and refreshed jwt if user is auth'd
 */
function ajaxExit($code, $data = null) {
    global $clientIpAddress, $autoJaxRequest;
    // ajaxExit can be reached from the global error/exception handlers before $autoJaxRequest is built
    // (e.g. a throwable during config assembly). guard so this terminal helper never throws a second time
    // and masks the original error -- the ?? uses isset() semantics, so it is safe on a null $autoJaxRequest.
    $service = $autoJaxRequest->service ?? "(no service defined)";
    if ( ! $code) {
        dieGracefully(SERVER_ERROR, "ajaxExit called with no http code :: " . json_encode($autoJaxRequest));
    }
    $uid = getUserId();
    if (($code === OKAY || $code === NO_CONTENT) && $uid > 0) {
        // only an authenticated round trip clears IP throttle state. unauthenticated public endpoints
        // (login attempts, clientLog, registration) must not be able to wipe their own throttle counter
        // simply by "succeeding", which would otherwise neuter throttling on exactly the abusable paths
        resetIpThrottle($clientIpAddress);
    }
    if ($data !== null) {
        if (dataProhibited($code) || jwtOnlyAndOptional($code)) {
            // jwtOnlyAndOptional codes (413) carry no business data -- the only payload they may carry is
            // the refreshed jwt, which ajaxExit injects itself below. a service supplying its own data is an error
            dieGracefully(
                SERVER_ERROR,
                "{$service} is trying to send data with http code {$code}, which is not allowed :: " . json_encode($autoJaxRequest)
            );
        } // else data is required or allowed
        if ( ! is_object($data) && ! is_array($data)) {
            $data = (object)[ $data ]; // convenience for calling code if they just sent a string or something
        } // else, if it's already an object or array, it better be well-formed for our ajax protocol
        if (is_array($data)) {
            $data = (object)$data; // for convenience below, data is now always an object
        }
        if (isset($data->file) && $data->file) { // doing a file download
            if ($code !== OKAY) {
                dieGracefully(
                    SERVER_ERROR,
                    "{$service} is trying to download a file with a non-OKAY status code: {$code} :: " . json_encode($autoJaxRequest)
                );
            }
            if ( ! isset($autoJaxRequest->fileName)    ||
                ! $autoJaxRequest->fileName            ||
                ! is_string($autoJaxRequest->fileName) ||
                trim($autoJaxRequest->fileName) === ""
            ) {
                dieGracefully(
                    SERVER_ERROR,
                    "{$service} wants to download a file but did not provide the file name :: " . json_encode($autoJaxRequest)
                );
            }
            $filenameUtf8  = basename($autoJaxRequest->fileName);
            $filenameAscii = preg_replace('/[^A-Za-z0-9._ -]/', "_", $filenameUtf8);
            if ( ! $filenameAscii || trim($filenameAscii) === "") {
                $filenameAscii = "download.bin";
            }
            // serve every download as an opaque attachment, never something the browser will render in
            // place. octet-stream plus Content-Disposition: attachment forces a save, and the global
            // X-Content-Type-Options: nosniff (see ajaxPort.php) stops the browser from guessing a
            // renderable type from the bytes. this is what makes a stored SVG safe without altering it: an
            // SVG can carry script, but script runs only when the file is rendered as a top-level document,
            // which this path never allows. script-src 'none' is belt-and-suspenders for the same case --
            // even if some context did render it, no embedded script could execute
            emitContentTypeHeader("octet");
            header("Content-Security-Policy: script-src 'none'");
            header(
                "Content-Disposition: attachment; " .
                "filename=\"" . $filenameAscii . "\"; " .
                "filename*=UTF-8''" . rawurlencode($filenameUtf8)
            );
            header("Content-Length: " . mb_strlen($data->file, "8bit"));
            http_response_code($code);
            echo $data->file;
            exit;
        } // else not doing a file download, see below
    }
    else { // no data
        if (dataRequired($code)) {
            dieGracefully(
                SERVER_ERROR,
                "{$service} sent code {$code} with no data (data required for this code) :: " . json_encode($autoJaxRequest));
        }
        if ($uid && responseCarriesSessionJwt($code)) {
            // refresh the auth token for an authenticated user. a fresh jwt is an envelope concern, not
            // business data, so it may ride along even on a code that prohibits business data -- but only
            // where a body is permitted and we want to extend the session. CONTENT_TOO_LARGE qualifies (the
            // session is healthy and the user will retry with a smaller payload), so its only allowed data
            // is the jwt. NOT_FOUND does not (by policy), NO_CONTENT cannot (HTTP forbids a 204 body --
            // the keepAlive 200-trick covers refresh-with-no-content), and __unauthenticated__ (401) must
            // not (the session is dead -- see responseCarriesSessionJwt). filled with the jwt below
            $data = (object)[ ];
        }
        else {
            // just a code, no responsive data, and no auth'd user -- presumably got here from dieGracefully()
            header_remove("Content-Type");
            header("Content-Length: 0");
            http_response_code($code);
            exit;
        }
    }
    // we have a data object and are not downloading a file
    // gate the refresh on responseCarriesSessionJwt -- the same decision used for the no-data branch above --
    // so a code that does not represent a live session never hands back a usable token. without this, a
    // __unauthenticated__ (401) carrying a reason payload (e.g. "session revoked") would reach here with a
    // hydrated uid and mint a fresh, valid session jwt, silently defeating the revocation it is reporting
    if ($uid && $data && responseCarriesSessionJwt($code)) {
        // include a fresh auth token to extend the session. carry the original session start forward so the
        // refresh cannot push the session past its absolute maximum. a brand-new login carries no start, so it
        // begins a fresh window from now (session.php stamps sessionStart explicitly)
        $sessionStart = (isset($autoJaxRequest->sessionStart) && is_int($autoJaxRequest->sessionStart))
            ? $autoJaxRequest->sessionStart
            : time();
        // carry the session id forward too, unchanged, so single-session enforcement keeps matching as the token
        // rotates. login and the cross-page exchange seed it on the request. a normal refresh gets it from the
        // incoming token (ajaxPort copies payload->sessionId onto the request). null only when absent, in which
        // case createJwt mints a fresh one -- harmless in unlimited mode, which never reads it
        $sessionId = (isset($autoJaxRequest->sessionId) && is_string($autoJaxRequest->sessionId))
            ? $autoJaxRequest->sessionId
            : null;
        $data->jwt = createJwt($uid, "session", null, null, null, $sessionStart, $sessionId);
    }
    // send data as json
    try {
        $data = json_encode($data); // will return false on failure
    }
    catch (\Throwable $thr) {
        $data = false; // another way to fail
    }
    if ($data === false) {
        dieGracefully(
            SERVER_ERROR,
            "Failed to encode json response: " . json_last_error_msg() . " :: " . json_encode($autoJaxRequest)
        );
    }
    emitContentTypeHeader("json");
    header("Content-Length: " . ($data !== null ? mb_strlen($data, "8bit") : 0));
    http_response_code($code);
    echo $data;
    exit;
}

// to avoid application-specific code needing to know to do the [ "reason" => "unauthorized" ] trick that
// distinguishes FORBIDDEN for the specific reason that the user lacks authorization for the requested service
// versus FORBIDDEN for any other reason (that we presume is an attack)
function exitUnauthorized() {
    ajaxExit(__unauthorized__, [ "reason" => "unauthorized" ]);
}
