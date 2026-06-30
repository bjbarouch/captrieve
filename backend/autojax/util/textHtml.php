<?php

namespace AutoJax;

/**
 * @file textHtml.php
 *
 * a small, general-purpose converter from a limited markdown subset to safe HTML, plus a rough HTML-to-text
 * helper for the reverse direction. nothing here is specific to email (the email body contract lives in
 * emailBodies.php). these are limited, dependency-free text helpers.
 *
 * SECURITY -- plainTextToHtml() is safe against HTML injection by construction:
 *   1. the entire input is HTML-escaped (htmlspecialchars, ENT_QUOTES) BEFORE any markdown is interpreted,
 *      so a caller's literal "<script>" is already "&lt;script&gt;" -- inert text -- by the time markers
 *      are read. it renders visibly rather than executing (fail-safe, fail-visible).
 *   2. the only tags in the output are ones this file emits itself, from a fixed set:
 *        <p> <br> <h1> <h2> <ul> <ol> <li> <strong> <i> <a>
 *      a caller's own tags are never echoed back.
 *   3. the one attribute emitted, href, is built only from a URL matched by the https?:// autolink pattern
 *      (so no javascript:/data: scheme can ever be linkified), and its value was escaped in step 1 (so a
 *      quote cannot break out of href="...").
 * the practical consequence: there is no need to trust the caller to escape anything -- escaping is
 * unconditional. (this is also why emailBodies.php no longer accepts raw HTML: the markdown path makes the
 * unsafe passthrough unnecessary.)
 *
 * supported markdown subset (deliberately flat -- no nesting, which is where markdown parsers grow bugs):
 *   - paragraphs        blank-line separated. a single newline within a paragraph becomes <br>
 *   - headings          "# " -> <h1>, "## " -> <h2> (three or more #'s render literally)
 *   - unordered lists   lines starting "* " or "- " -> <ul><li>. consecutive lines form one list
 *   - ordered lists     lines starting "1. " (any number) -> <ol><li>. numbers are not preserved
 *   - bold              **text** -> <strong>
 *   - italic            *text*   -> <i>   (single asterisk, markdown-standard. no underscore forms)
 *   - links             bare http(s):// URLs -> <a>
 * limits, by design: lists are single-level only (indentation is not interpreted), one line per list item,
 * and a blank line ends a list.
 *
 * @author
 *   Bennett Barouch
 * @copyright
 *   Copyright 2025-2026 Bennett Barouch
 *   SPDX-License-Identifier: Apache-2.0
 */

/**
 * true if a line begins a block-level construct (heading or list item), so paragraph accumulation stops at it.
 *
 * @param string $line  a single line, no trailing newline
 * @return bool
 */
function startsBlock($line) {
    return (bool)preg_match('/^(?:#{1,2}[ \t]+\S|[*-][ \t]+\S|\d+\.[ \t]+\S)/', $line);
}

/**
 * apply inline markdown to a single block's text: escape everything first (the injection floor), protect
 * bare http(s) URLs from the emphasis pass by masking them, convert **bold** and *italic*, then restore the
 * URLs as <a> links. no <br> is added here -- block callers decide line-break handling.
 *
 * order matters: escape, then mask URLs, then bold (**) before italic (*) so a double marker is consumed
 * whole, then restore. emphasis requires non-space at both edges and forbids spanning a newline.
 *
 * @param string $text  already-classified block text (may contain newlines for a multi-line paragraph)
 * @return string       safe inline HTML
 */
function inlineToHtml($text) {
    $escaped = htmlspecialchars((string)$text, ENT_QUOTES, "UTF-8");
    $links   = [ ];
    $masked  = preg_replace_callback(
        '#(https?://[^\s<]+)#',
        function ($m) use (&$links) {
            $token         = "\x00L" . count($links) . "\x00";
            $links[$token] = "<a href=\"{$m[1]}\">{$m[1]}</a>";
            return $token;
        },
        $escaped
    );
    // **bold** first, then *italic*, so the single-asterisk rule cannot split a double marker. each requires
    // non-space at both edges. the italic inner class forbids '*' so it never reaches across a bold run
    $masked = preg_replace('/\*\*(\S(?:[^\n]*?\S)?)\*\*/', '<strong>$1</strong>', $masked);
    $masked = preg_replace('/\*(\S(?:[^*\n]*?\S)?)\*/',    '<i>$1</i>',           $masked);
    return strtr($masked, $links);
}

/**
 * convert a limited markdown subset to safe HTML. see the file header for the supported subset and the
 * injection-safety argument. the input is processed line by line into flat blocks. each block's text runs
 * through inlineToHtml(), which escapes before interpreting anything.
 *
 * @param string $text  plain text / limited markdown (CRLF, CR, and LF newlines all accepted)
 * @return string       safe HTML. empty string for empty or whitespace-only input
 */
function plainTextToHtml($text) {
    $lines = explode("\n", str_replace([ "\r\n", "\r" ], "\n", (string)$text));
    $count = count($lines);
    $html  = "";
    $i     = 0;
    while ($i < $count) {
        $line = $lines[$i];
        if (trim($line) === "") {
            ++$i;
            continue;
        }
        if (preg_match('/^(#{1,2})[ \t]+(.*\S)[ \t]*$/', $line, $m)) {
            $level = strlen($m[1]);
            $html .= "<h{$level}>" . inlineToHtml($m[2]) . "</h{$level}>";
            ++$i;
            continue;
        }
        if (preg_match('/^[*-][ \t]+(.*\S)[ \t]*$/', $line, $m)) {
            $items = "";
            while ($i < $count && preg_match('/^[*-][ \t]+(.*\S)[ \t]*$/', $lines[$i], $m)) {
                $items .= "<li>" . inlineToHtml($m[1]) . "</li>";
                ++$i;
            }
            $html .= "<ul>{$items}</ul>";
            continue;
        }
        if (preg_match('/^\d+\.[ \t]+(.*\S)[ \t]*$/', $line, $m)) {
            $items = "";
            while ($i < $count && preg_match('/^\d+\.[ \t]+(.*\S)[ \t]*$/', $lines[$i], $m)) {
                $items .= "<li>" . inlineToHtml($m[1]) . "</li>";
                ++$i;
            }
            $html .= "<ol>{$items}</ol>";
            continue;
        }
        // paragraph: gather consecutive lines until a blank line or a block-starting line
        $paragraph = [ ];
        while ($i < $count && trim($lines[$i]) !== "" && ! startsBlock($lines[$i])) {
            $paragraph[] = $lines[$i];
            ++$i;
        }
        $html .= "<p>" . nl2br(inlineToHtml(implode("\n", $paragraph))) . "</p>";
    }
    return $html;
}

/**
 * recover readable plain text from HTML. <br> and the closing of common block tags (p, div, h1-h6, li, tr)
 * become newlines, all remaining tags are stripped, and entities are decoded back to their characters. it
 * is deliberately limited -- it recovers legible text, not layout, list markers, or emphasis markers. it is
 * not used by the email path (which keeps the markdown source as its plain-text part) but remains available
 * as a general helper.
 *
 * @param string $html  HTML input
 * @return string       plain text, trimmed of leading and trailing whitespace
 */
function htmlToPlainText($html) {
    $text = preg_replace('#<\s*br\s*/?\s*>#i', "\n", (string)$html);
    $text = preg_replace('#</\s*(?:p|div|h[1-6]|li|tr)\s*>#i', "\n", $text);
    $text = strip_tags($text);
    return trim(html_entity_decode($text, ENT_QUOTES, "UTF-8"));
}
