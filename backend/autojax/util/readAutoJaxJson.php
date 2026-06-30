<?php

namespace AutoJax;

/**
 * @file readAutoJaxJson.php
 *
 * reads and parses autojax.json, stripping JSON5-style comments first
 *
 * PHP Version 8.5
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

require_once __DIR__ . "/util.php";

/**
 * read, strip comments from, and parse autojax.json
 *
 * supports // line comments and /* block comments outside of string values.
 * the file path is resolved from $autoJaxJsonPath set in autojax-secrets.php.
 *
 * @param  string $path  absolute filesystem path to autojax.json
 * @return object        parsed config object
 */
function readAutoJaxJson($path) {
    if ( ! file_exists($path)) {
        error_log("autojax fatal: autojax.json not found at: {$path}");
        exit(1);
    }
    $raw      = file_get_contents($path);
    $stripped = stripJson5Comments($raw);
    $config   = json_decode($stripped);
    if ($config === null) {
        error_log("autojax fatal: autojax.json parse error at {$path}: " . json_last_error_msg());
        exit(1);
    }
    return $config;
}
