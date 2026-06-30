<?php

namespace AutoJax;

/**
 * @file sendEmail.php
 *
 * wrapper around PHPMailer for sending HTML emails
 *
 * auto-reroutes email to the tech support address when in development and test environments
 * makes sure HTML encoding of message bodies is safe
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

use PHPMailer\PHPMailer\PHPMailer; // version 6 or later
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/util.php"; // always require_once this file first
require_once __DIR__ . "/emailBodies.php"; // normalizeEmailBodies(), textToHtml(), htmlToText()
require_once __DIR__ . "/vendor/PHPMailer/src/Exception.php";
require_once __DIR__ . "/vendor/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/vendor/PHPMailer/src/PHPMailer.php";

/**
 * Dev and test only: append a would-be email to a spool file so e2e tests can read the links that real
 * delivery would otherwise carry (account confirmation, password reset). The caller gates this on
 * inDevAndTest(), so it is never reached in production. One JSON record per line: time, to, subject, body.
 *
 * @param object            $cfg     the active autojax config, used only for the log directory
 * @param string|array|null $to      recipient(s) as passed to sendEmail, before any defaulting
 * @param string            $subject the email subject
 * @param string            $message the email body, as the caller supplied it
 * @return void
 */
function spoolTestEmail($cfg, $to, $subject, $message) {
    if ( ! isset($cfg->log->error)) {
        return; // no log directory is configured, so there is nowhere safe to write the spool
    }
    $recipients = $to === null ? [ ] : (is_array($to) ? array_values($to) : [ $to ]);
    $record     = json_encode([
        "time"    => date("c"),
        "to"      => $recipients,
        "subject" => $subject,
        "body"    => $message
    ]);
    if ($record === false) {
        return; // a body that will not json-encode is not worth failing a pretend-send over
    }
    appendToLog(dirname($cfg->log->error) . "/autoJaxTestMail.log", $record);
}

/**
 * sends an email using PHPMailer and the settings defined in the configuration file
 * falls back to tech support email if no recipient is provided
 * in development or test environments, reroutes all mail to tech support and annotates intended recipients in the body
 *
 * @param string        $subject subject line of the email
 * @param string        $message body content of the email
 * @param string|array  $to      (Optional) recipients
 * @param string|null   $from    (Optional) sender - defaults to noReply or techSupport in config
 * @param string|array  $cc      (Optional) CC recipients
 * @param string|array  $bcc     (Optional) BCC recipients
 * @param stdClass|null $cfg     (Optional) Configuration object. if not provided, it will be loaded.
 *
 * @return int returns NO_CONTENT (204) on success, SERVICE_UNAVAILABLE (503) on send failure,
 *             or SERVER_ERROR (500) if an exception is thrown
 */
function sendEmail($subject, $message, $to = null, $from = null, $cc = null, $bcc = null, $cfg = null) {
    global $autoJaxConfig;
    if ( ! $cfg) { // typical case
        $cfg = $autoJaxConfig;
    }
    if ( ! $cfg) {
        throw new \Exception("cannot send email if it is not supported in the config file");
    }
    // hereafter we assume without checking that $cfg holds the required information int he required places
    if (inAutomatedTesting()) {
        // an automated test run has no mail server, so transactional links (account confirmation, password
        // reset) would otherwise vanish. dump the would-be message to a spool file the tests can read, and
        // skip delivery. checked before the email config so a test run behaves the same regardless of it.
        spoolTestEmail($cfg, $to, $subject, $message);
        return NO_CONTENT; // pretend to have functioned successfully
    }
    if ($cfg->email->turnOff) {
        return NO_CONTENT; // email delivery is off by configuration -- nothing is sent
    }
    if ( ! $to) {
        $to = $cfg->email->techSupport;
    }
    if (is_string($to)) {
        $to = [ $to ];
    }
    if ( ! $cc) {
        $cc = [ ];
    }
    if (is_string($cc)) {
        $cc = [ $cc ];
    }
    if ( ! $bcc) {
        $bcc = [ ];
    }
    if (is_string($bcc)) {
        $bcc = [ $bcc ];
    }
    if ( ! $from) {
        $from = isset($cfg->email->noReply) ? $cfg->email->noReply : $cfg->email->techSupport;
    }
    // resolve the caller's message into the text + html pair the email always carries (see normalizeEmailBodies).
    // a plain-string caller gets a safe html body derived for it. a caller may instead supply real authored html
    [ $textBody, $htmlBody ] = normalizeEmailBodies($message);
    try {
        $mail = new PHPMailer();
        $mail->SMTPDebug = 0;
        // transport: "sendmail" hands the message to the local mail system (the sendmail interface postfix and
        // exim provide), which queues and delivers it out of process. "smtp" instead opens an authenticated
        // connection to email.server. ajaxPort validates this to exactly one of the two at startup. this guard
        // is defense for any caller that assembled config without it -- never silently infer a transport
        $transport = $cfg->email->transport ?? null;
        if ($transport !== "smtp" && $transport !== "sendmail") {
            reportError(
                SERVER_ERROR,
                "sendEmail: emailTransport is not \"smtp\" or \"sendmail\" -- run mail-probe.sh and set it in autojax.json"
            );
            return SERVER_ERROR;
        }
        if ($transport === "smtp") {
            $mail->isSMTP();
            $mail->Host       = $cfg->email->server;
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->Username   = $cfg->email->user;
            $mail->Password   = $cfg->email->password;
        }
        else {
            $mail->isSendmail(); // the local mail system owns queuing, retry, and delivery
        }
        $mail->isHTML(true);
        $mail->CharSet    = "UTF-8";
        $mail->Body       = $htmlBody;  // real html, sent as given (derived from plain text when the caller sent a string)
        $mail->AltBody    = $textBody;  // plain-text alternative -- spam filters credit a message that carries both parts
        $mail->setFrom($from);
        if (inDevAndTest()) { // in dev & test, not production
            $mail->Subject = "D&T: " . $subject; // flag the message as a dev & test message
            // document rather than use the given to, cc, and bcc recipients, and send to tech support instead.
            // escape the recipient lists before embedding them in the html body so an odd address cannot inject markup
            $mail->Body =
                "<p>"  .
                    "to: "    . htmlspecialchars(implode(", ", $to),  ENT_QUOTES, "UTF-8") .
                    "; cc: "  . htmlspecialchars(implode(", ", $cc),  ENT_QUOTES, "UTF-8") .
                    "; bcc: " . htmlspecialchars(implode(", ", $bcc), ENT_QUOTES, "UTF-8") .
                "</p>" . $htmlBody;
            $mail->addAddress($cfg->email->techSupport);
            // when in dev & test, we are likely to be seen as a spam source for sending a lot of similar messages in a short
            // period of time
            // as such, the next line should usually be uncommented, but you can and should comment it out for a test or two of
            // email itself when needed
            return NO_CONTENT;
        }
        else { // in production
            $mail->Subject = $subject;
            for ($i = count($to) - 1; $i >= 0; --$i) {  // typically count($to)  === 1
                $mail->addAddress($to[$i]);
            }
            for ($i = count($cc) - 1; $i >= 0; --$i) {  // typically count($cc)  === 0
                $mail->addCC($cc[$i]);
            }
            for ($i = count($bcc) - 1; $i >= 0; --$i) { // typically count($bcc) === 0
                $mail->addBCC($bcc[$i]);
            }
        }
        $result = $mail->send();
        if ( ! $result) {
            if (function_exists("xdebug_break")) {
                xdebug_break(); // so we can inspect the error message right here
            }
            reportError(SERVER_ERROR, "Could not send email, PHPMailer error: {$mail->ErrorInfo}");
        }
        return $result ? NO_CONTENT : SERVICE_UNAVAILABLE;
    }
    catch (\Throwable $thr) {
        return SERVER_ERROR;
    }
}
