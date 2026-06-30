<?php

namespace AutoJax;
// util/db.php

/**
 * @file db.php
 *
 * thin wrapper around mysqli
 *
 * opens a connection lazily, provides transaction helpers, and wraps prepared statements so errors get handled consistently
 * the main workhorses are
 *     for reading zero or more matching entire rows:
 *         dbFetchRows($purpose, $sql, $types = "", $params = [ ], $stream = false)
 *     for all other types of queries, including multiple rows but few of the columns:
 *         dbQuery($purpose, $sql, $types = "", $params = [ ])
 *
 * the connection is pinned to UTC (see dbOpen). always convert a TIMESTAMP/DATETIME read from the
 * database with dbTimestampToEpoch() -- never a bare strtotime() -- before comparing it to time() or a
 * jwt iat, so the comparison is correct regardless of the MySQL server's or PHP's display timezone.
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

/**
 * @var mysqli|null $conn global MySQLi connection handle.
 */
$conn = null;

/**
 * return the last MySQL error (statement first, then connection) as printable text (for logging)
 *
 * @param mysqli_stmt|null $stmt optional statement, else reads from connection
 * @return string                error text or empty string if none
 */
function lastDbError($stmt = null) {
    global $conn;
    if ($stmt instanceof \mysqli_stmt && $stmt->errno) {
        return " MySQL error {$stmt->errno}: {$stmt->error}";
    }
    if ($conn && $conn->errno) {
        return " MySQL error {$conn->errno}: {$conn->error}";
    }
    return "";
}

/**
 * open a connection to the MySQL database using credentials from the config file
 * if already open, the existing connection is reused
 *
 * @return mysqli MySQLi connection object.
 */
function dbOpen() {
    global $autoJaxConfig, $conn;
    if ($conn === null) {
        try {
            $conn = new \mysqli(
                $autoJaxConfig->db->host,
                $autoJaxConfig->db->user,
                $autoJaxConfig->db->password,
                $autoJaxConfig->db->database
            );
            if ( ! $conn) {
                // so caller can give more context in a separate error message
                throw new \Exception("Could not open database, but no exception thrown");
            }
            if ($conn->connect_errno) {
                // so caller can give more context in a separate error message
                throw new \Exception("Database connection failed: " . $conn->connect_error);
            }
        }
        catch (\Throwable $thr) {
            // so caller can give more context in a separate error message
            throw new \Exception(
                "Database connection failed: " . $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
            );
        }
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        // pin the connection to UTC so every TIMESTAMP value MySQL returns -- and every NOW() it computes --
        // is UTC wall-clock, independent of the MySQL server's own time_zone. PHP's date_default_timezone_set
        // tracks the operator's display timezone (for readable logs), so a stored timestamp must be parsed as
        // UTC (see dbTimestampToEpoch) to compare correctly against time() or a jwt iat. without this pin, a
        // server timezone that differs from the display timezone silently skews the session-revocation and
        // password-reset checks. the integration test bootstraps force the same alignment with SET time_zone
        $conn->query("SET time_zone = '+00:00'");
        // set the mysql version
        $result = $conn->query("SELECT VERSION()");
        $row = $result->fetch_row();
        $rawVersion  = $row[0];
        $isMariaDB   = stripos($rawVersion, 'mariadb') !== false;
        $numericPart = preg_replace('/[^0-9.].*$/', "", $rawVersion);
        $parts       = explode(".", $numericPart);
        $autoJaxConfig->db->mysql = (object)[
            "isMariaDB" => $isMariaDB,
            "major"     => (int)($parts[0] ?? 0),
            "minor"     => (int)($parts[1] ?? 0),
            "patch"     => (int)($parts[2] ?? 0)
        ];
    }
    return $conn;
}

// in mysql, $conn->begin_transaction() auto-commits any already pending transaction! bad!
// so, logically nested transactions have to merge themselves into a single transaction at the top level
$transactionDepth = 0;

/**
 * begin a database transaction
 *
 * @return void
 */
function dbBeginTransaction() {
    global $conn, $transactionDepth;
    if ($transactionDepth === 0) {
        try {
            $conn = dbOpen(); // can throw
            if ( ! $conn->begin_transaction()) {
                // so caller can give more context in a separate error message
                throw new \Exception("Could not begin transaction: " . $conn->error);
            }
        }
        catch (\Throwable $thr) {
            // so caller can give more context in a separate error message
            throw new \Exception(
                "Could not begin transaction: " .
                $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
            );
        }
    } // else we are in a "nested" transaction, so do nothing
    $transactionDepth++;
}

/**
 * roll back the current transaction if any
 * a nested call (depth greater than one) only decrements the depth and does not roll back, so the outermost
 * call performs the single real rollback
 *
 * @return void
 * @throws Exception when no database connection is open, when no transaction is open, or when the underlying
 *                   rollback itself fails
 */
function dbRollbackTransaction() {
    global $conn, $transactionDepth;
    if ( ! $conn) {
        // so caller can give more context in a separate error message
        throw new \Exception("Cannot rollback transaction: database is not open");
    }
    if ($transactionDepth === 0) {
        // so caller can give more context in a separate error message
        throw new \Exception("Cannot rollback transaction: no transaction is open");
    }
    if ($transactionDepth === 1) { // do a real rollback
        try {
            $conn->rollback();
        }
        catch (\Throwable $thr) {
            // so caller can give more context in a separate error message
            throw new \Exception(
                "Cannot rollback transaction: " . $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
            );
        }
    } // else, we are in a "nested" transaction, so do nothing
    $transactionDepth--;
}

/**
 * commit the current database transaction
 *
 * @return void
 */
function dbCommitTransaction() {
    global $conn, $transactionDepth;
    if ( ! $conn) {
        // so caller can give more context in a separate error message
        throw new \Exception("Cannot commit transaction: database is not open");
    }
    if ($transactionDepth === 0) {
        // so caller can give more context in a separate error message
        throw new \Exception("Cannot commit transaction: no transaction is open");
    }
    if ($transactionDepth === 1) { // do a real commit
        try {
            if ( ! $conn->commit()) {
                // so caller can give more context in a separate error message
                throw new \Exception("Could not commit transaction: " . $conn->error);
            }
        }
        catch (\Throwable $thr) {
            // so caller can give more context in a separate error message
            throw new \Exception(
                "Could not commit transaction: " .
                $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
            );
        }
    } // else, we are in a "nested" transaction, so do nothing
    $transactionDepth--;
}

/**
 * prepare a SQL statement
 *
 * @param string $sql     sql statement
 * @param string $purpose purpose of the statement, for error reporting
 * @return mysqli_stmt prepared statement object
 */
function dbPrep($sql, $purpose) {
    global $conn;
    try {
        $conn = dbOpen(); // can throw
        $stmt = $conn->prepare($sql);
        if ( ! $stmt) {
            // so caller can give more context in a separate error message
            throw new \Exception("Could not prepare statement to {$purpose}: " . $conn->error);
        }
    }
    catch (\Throwable $thr) {
        // so caller can give more context in a separate error message
        throw new \Exception(
            "Could not prepare statement to {$purpose}: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    return $stmt;
}

/**
 * execute a prepared sql statement
 *
 * handles both mysqli_sql_exception (from MYSQLI_REPORT_STRICT) and traditional error checking
 * error 1062 (duplicate key) is allowed to pass through to calling code
 *
 * @param mysqli_stmt $stmt    prepared statement object
 * @param string      $purpose purpose ot the statement, for error reporting
 * @return void
 */
function dbExec(&$stmt, $purpose) {
    global $conn;
    if ( ! ($stmt instanceof \mysqli_stmt)) {
        // so caller can give more context in a separate error message
        throw new \Exception("Invalid or unprepared statement for: {$purpose}");
    }
    try {
        $result = $stmt->execute();
        if ( ! $result) {
            $errno = $stmt->errno ? $stmt->errno : ($conn->errno ?? 0);
            $error = $stmt->error ? $stmt->error : ($conn->error ?? "unknown error");
            if ($errno !== 1062) {
                // so caller can give more context in a separate error message
                throw new \Exception("Could not execute statement to {$purpose}: " . $error);
            }
        }
    }
    catch (\Throwable $thr) {
        $errno = $stmt->errno ? $stmt->errno : ($conn->errno ?? 0);
        if ( ! $errno) {
            $errno = (int)$thr->getCode();
        }
        if ( ! $errno) {
            // mysqli_sql_exception message starts with the error number in some PHP versions
            preg_match('/^\\(?(\\d+)\\)?/', $thr->getMessage(), $m);
            $errno = isset($m[1]) ? (int)$m[1] : 0;
        }
        $isDuplicateKey = ($errno === 1062) ||
            (strpos($thr->getMessage(), "Duplicate entry") !== false);
        if ( ! $isDuplicateKey) {
            // so caller can give more context in a separate error message
            throw new \Exception(
                "Could not execute statement to {$purpose}: " .
                $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
            );
        } // else, errno is 1062, let caller decide what to do about it
    }
}

/**
 * prepares, binds parameters (if any), executes, and returns result in bound variables
 *
 * @param string $purpose purpose of the statement, for error logging
 * @param string $sql     sql query string with ? placeholders
 * @param string $types   type string for `bind_param`
 * @param array  $params  parameters to bind
 *
 * @return mysqli_stmt executed prepared statement
 */
function dbQuery($purpose, $sql, $types = "", $params = [ ]) {
    if (is_string($purpose)) {
        $purpose = trim($purpose);
    }
    if ( ! is_string($purpose) || $purpose === "") {
        // so caller can give more context in a separate error message
        throw new \Exception("Argument purpose must be a non-blank string");
    }
    if (is_string($sql)) {
        $sql = trim($sql);
    }
    if ( ! is_string($sql) || $sql === "") {
        // so caller can give more context in a separate error message
        throw new \Exception("Argument sql must be a non-blank string");
    }
    if ( ! is_array($params)) {
        $params = [ $params ];
    }
    $n = count($params);
    if ($n !== strlen($types)) {
        // so caller can give more context in a separate error message
        throw new \Exception("Miscount between types and params arguments");
    }
    try {
        $stmt = dbPrep($sql, $purpose); // can throw
        if ($n) { // bind params
            if ( ! $stmt->bind_param($types, ...$params)) {
                // so caller can give more context in a separate error message
                throw new \Exception("Could not bind parameters for {$purpose}: " . $stmt->error);
            }
        } // else, no params to bind
    }
    catch (\Throwable $thr) {
        // so caller can give more context in a separate error message
        throw new \Exception(
            "Could not bind parameters for {$purpose}: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    try {
        dbExec($stmt, $purpose); // can throw, but 1062 (duplicate key) is allowed through for app processing
    }
    catch (\Throwable $thr) {
        $errno = $stmt->errno ? $stmt->errno : 0;
        if ( ! $errno) {
            $errno = (int)$thr->getCode();
        }
        $isDuplicateKey = ($errno === 1062) ||
            (strpos($thr->getMessage(), "Duplicate entry") !== false);
        if ( ! $isDuplicateKey) {
            // so caller can give more context in a separate error message
            throw new \Exception(
                "Could not execute query for {$purpose}: " .
                $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
            );
        } // else 1062 -- let caller inspect $stmt->errno
    }
    return $stmt;
}

/**
 * fetch multiple rows for a SQL query and close the statement
 *
 * @param string $purpose purpose of the query, for error reporting
 * @param string $sql     SQL query string with ? placeholders
 * @param string $types   type string for `bind_param`
 * @param array  $params  parameters to bind
 * @param bool   $stream  true to return a generator for large result sets
 * @return [ ] | generator    array of rows or generator
 */
function dbFetchRows($purpose, $sql, $types = "", $params = [ ], $stream = false) {
    try {
        $stmt = dbQuery($purpose, $sql, $types, $params); // can throw
    }
    catch (\Throwable $thr) {
        // so caller can give more context in a separate error message
        throw new \Exception(
            "Could not fetch rows for {$purpose}: " .
            $thr->getMessage() . " in " . $thr->getFile() . ":" . $thr->getLine()
        );
    }
    // get metadata to determine if there is a result set
    $meta = $stmt->result_metadata();
    if ($meta === false) {
        $stmt->close();
        // calling code can provide an error message with more helpful context
        throw new \Exception("cannot fetch rows on a statement that cannot produce a result set");
    }
    if (empty($stream)) { // return all rows at once
        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
    // return a generator, to limit memory consumption for expectedly large result sets
    // note that generators function like arrays in a foreach loop, but cannot be
    // given to count(), or be accessed with subscripts, cannot be revisited, …
    $fields = $meta->fetch_fields();
    $bindVars = [ ];
    $refs = [ ];
    foreach ($fields as $i => $field) {
        $bindVars[$i] = null;
        $refs[$i]     = &$bindVars[$i];
    }
    $stmt->bind_result(...$refs);
    return (function() use ($stmt, $fields, &$bindVars) {
        while ($stmt->fetch()) {
            $row = [ ];
            foreach ($fields as $i => $field) {
                $row[$field->name] = $bindVars[$i];
            }
            yield $row;
        }
        $stmt->close();
    })();
}

/**
 * retrieve the most recent AUTO_INCREMENT value generated on the current connection
 *
 * @return int|null last inserted AUTO_INCREMENT value
 */
function dbLastInsertId() {
    global $conn;
    return $conn ? $conn->insert_id : null;
}

/**
 * convert a MySQL DATETIME/TIMESTAMP string to a Unix epoch, interpreting it as UTC.
 *
 * the connection is pinned to UTC in dbOpen(), so every TIMESTAMP MySQL returns and every NOW() it
 * computes is UTC wall-clock. parsing with an explicit UTC zone makes the result independent of PHP's
 * date_default_timezone_set (which tracks the operator's display timezone for logs). this is what keeps a
 * comparison of a stored timestamp against time() or a jwt iat correct regardless of host or server timezone.
 * always use this -- never a bare strtotime() -- on a timestamp read from the database.
 *
 * @param string $ts MySQL timestamp string, e.g. "2026-06-21 16:00:00"
 * @return int        Unix epoch seconds (UTC)
 */
function dbTimestampToEpoch($ts) {
    return strtotime($ts . " UTC");
}
