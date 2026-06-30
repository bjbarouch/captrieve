<?php

namespace AutoJax;

require_once __DIR__ . "/textHtml.php"; // plainTextToHtml()

/**
 * @file emailBodies.php
 *
 * the email-specific body contract, extracted from sendEmail.php so it can be unit tested in isolation
 * (sendEmail.php pulls in PHPMailer and declares the send function, neither of which a body-shaping test
 * should have to load). sendEmail.php require_once's this file.
 *
 * the contract: an email always carries both a plain-text part and an HTML part (spam filters credit a
 * message that has both). the caller supplies a single plain-text / limited-markdown string. the markdown
 * source itself IS the plain-text part, and plainTextToHtml() renders the HTML part.
 *
 * there is deliberately NO raw-HTML input. plainTextToHtml() escapes its input before interpreting any
 * markdown (see textHtml.php), so a caller cannot inject HTML into an email no matter how careless the
 * message is -- escaping is structural, not the caller's responsibility. a raw-HTML passthrough would
 * reintroduce exactly that injection risk, so it does not exist.
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

/**
 * resolve a caller's message into the [ plain-text, HTML ] pair every email carries. the message is a plain
 * text / limited-markdown string: it is returned verbatim as the text body, and rendered to safe HTML for
 * the HTML body.
 *
 * @param string $message  plain text / limited markdown (see textHtml.php for the supported subset)
 * @return array{0: string, 1: string}  [ textBody, htmlBody ]
 */
function normalizeEmailBodies($message) {
    $text = (string)$message;
    return [ $text, plainTextToHtml($text) ];
}
