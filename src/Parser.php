<?php

declare(strict_types=1);

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Symfony\Component\Yaml\Yaml;
use Throwable;

final readonly class Parser
{
    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
    ) {}

    public function parse(string $input): ParsedMarkdown
    {
        return parse_markdown($input, $this->highlighter);
    }
}

// active highlighter. set by parse_markdown, read by emit_code_tag. avoids threading
// the dependency through every signature.
final class ParserState
{
    public static ?Highlighter $highlighter = null;
}

// emit_inline context ids. DIV/TABLE alias QUOTE/PARAGRAPH for call-site clarity.
final class InlineContext
{
    public const int PARAGRAPH = 1;
    public const int BOLD      = 2;
    public const int ITALIC    = 3;
    public const int LINK      = 4;
    public const int STRIKE    = 5;
    public const int QUOTE     = 6;
    public const int DIV       = self::QUOTE;     // divs nest blockquotes, so same inline rules as QUOTE.
    public const int TABLE     = self::PARAGRAPH; // table cells take the same inline rules as paragraphs.

    public const int DEPTH_MAX = 64;

    // invariant: every char here has a matching arm in emit_inline.
    public const array STOP_CHARS = [
        self::PARAGRAPH => '*_[!`',
        self::BOLD      => '_[~',
        self::ITALIC    => '*[~',
        self::LINK      => '*_~',
        self::STRIKE    => '*_[',
        self::QUOTE     => '>*_[!',
    ];
}

// scan_blocks dispatches on the first char of each block to a consume_X, which
// either emits html directly or hands a slice to emit_inline. all consume_X /
// emit_X take (source, position, ..., string &$out) and return the offset past
// what they consumed; output goes straight into $out, recursion never copies.
function parse_markdown(string $source, ?Highlighter $highlighter): ParsedMarkdown
{
    ParserState::$highlighter = $highlighter;
    $front_matter = [];
    $html = '';
    scan_blocks($source, $html, $front_matter);

    return new ParsedMarkdown($html, $front_matter);
}

// block scanner.
// add a block rule: match arm below + consume_X helper returning the new position.
function scan_blocks(
    string $source,
    string &$out,
    array &$front_matter,
): void {
    $length_bytes = \strlen($source);
    $position = 0;
    // rows of the currently-open table, if any: [[cells, is_header], ...].
    $table_rows = [];

    while ($position < $length_bytes) {
        $char = $source[$position];

        if ($char === "\n" || $char === "\r") {
            if ($table_rows !== []) {
                render_table($table_rows, $out);
                $table_rows = [];
            }
            $newline_end = $position + \strspn($source, "\r\n", $position);
            $out .= \substr($source, $position, $newline_end - $position);
            $position = $newline_end;
            continue;
        }

        if ($char !== '|' && $table_rows !== []) {
            render_table($table_rows, $out);
            $table_rows = [];
        }

        $position = match (true) {
            // front matter only at byte 0; ruler --- elsewhere falls into the ruler arm.
            $position === 0 && $char === '-' && \substr_compare($source, '---', 0, 3) === 0
            => consume_front_matter($source, $position, $length_bytes, $front_matter),
            $char === '#'
            => consume_heading($source, $position, $out),
            $char === '>'
            => emit_blockquote($source, $position, $length_bytes, 0, $out),
            $char === '`' && \substr_compare($source, '```', $position, 3) === 0
            => consume_pre($source, $position, $length_bytes, $out),
            $char === ':' && \substr_compare($source, ':::', $position, 3) === 0
            => consume_div($source, $position, $length_bytes, $out),
            ($char === '=' || $char === '-') && \substr_compare($source, "$char$char$char", $position, 3) === 0
            => consume_ruler($source, $position, $char, $out),
            $char === '-' && list_marker_length($source, $position, $length_bytes, false) > 0
            => consume_list($source, $position, $length_bytes, false, $out),
            \ctype_digit($char) && list_marker_length($source, $position, $length_bytes, true) > 0
            => consume_list($source, $position, $length_bytes, true, $out),
            $char === '|'
            => consume_table_row($source, $position, $table_rows),
            $char === '<'
            => consume_html($source, $position, $length_bytes, $out),
            default
            => consume_paragraph($source, $position, $out),
        };
    }

    if ($table_rows !== []) {
        render_table($table_rows, $out);
    }
}

//
// block consumers.
//

function consume_ruler(string $source, int $position, string $char, string &$out): int
{
    $out .= '<hr/>';
    return $position + \strspn($source, $char, $position);
}

function consume_heading(string $source, int $position, string &$out): int
{
    $position_end = $position + \strcspn($source, "\r\n", $position);
    $line = \substr($source, $position, $position_end - $position);
    $level = \strspn($line, '#');
    $content = \trim(\substr($line, $level));
    $slug = \str_replace(' ', '-', \strtolower($content));
    $out .= "<h{$level} id=\"{$slug}\">{$content}</h{$level}>";
    return $position_end;
}

function consume_paragraph(
    string $source,
    int $position,
    string &$out,
): int {
    // trailing newline run is part of the slice (matches OO ParagraphRule).
    $line_end = $position + \strcspn($source, "\r\n", $position);
    $paragraph_end = $line_end + \strspn($source, "\r\n", $line_end);
    $out .= '<p>';
    emit_inline($source, $position, $paragraph_end, InlineContext::PARAGRAPH, 0, $out);
    $out .= '</p>';
    return $paragraph_end;
}

// `- ` / `\d+. ` markers (trailing space required). children are 2-space-indented
// lines, recursively rendered as the same kind (mixing kinds silently drops).
function consume_list(
    string $source,
    int $position,
    int $length_bytes,
    bool $ordered,
    string &$out,
): int {
    $tag = $ordered ? 'ol' : 'ul';
    $out .= "<{$tag}>";

    while (($marker_length = list_marker_length($source, $position, $length_bytes, $ordered)) > 0) {
        $position += $marker_length;

        $line_end = $position + \strcspn($source, "\r\n", $position);
        $content = \trim(\substr($source, $position, $line_end - $position));
        $position = $line_end + \strspn($source, "\r\n", $line_end);

        // 2-space-indented child lines, prefix stripped; deeper indents are kept
        // so further nesting de-indents correctly on recursion.
        $child_content = '';
        while (
            $position + 1 < $length_bytes
            && $source[$position] === ' '
            && $source[$position + 1] === ' '
        ) {
            $position += 2;
            $child_line_end = $position + \strcspn($source, "\r\n", $position);
            $child_content .= \substr($source, $position, $child_line_end - $position) . "\n";
            $position = $child_line_end + \strspn($source, "\r\n", $child_line_end);
        }

        $out .= '<li>';
        emit_inline($content, 0, \strlen($content), InlineContext::PARAGRAPH, 0, $out);

        if (
            $child_content !== ''
            && list_marker_length($child_content, 0, \strlen($child_content), $ordered) > 0
        ) {
            consume_list($child_content, 0, \strlen($child_content), $ordered, $out);
        }

        $out .= '</li>';
    }

    $out .= "</{$tag}>";
    return $position;
}

// byte length of a list marker, 0 if none. trailing space required.
function list_marker_length(
    string $source,
    int $position,
    int $length_bytes,
    bool $ordered,
): int {
    if ($position >= $length_bytes) {
        return 0;
    }

    if (! $ordered) {
        if ($source[$position] !== '-') {
            return 0;
        }
        if ($position + 1 >= $length_bytes || $source[$position + 1] !== ' ') {
            return 0;
        }
        return 2;
    }

    $digit_run = \strspn($source, '0123456789', $position);
    if ($digit_run === 0) {
        return 0;
    }
    $after_digits = $position + $digit_run;
    if ($after_digits + 1 >= $length_bytes) {
        return 0;
    }
    if ($source[$after_digits] !== '.' || $source[$after_digits + 1] !== ' ') {
        return 0;
    }
    return $digit_run + 2;
}

function consume_table_row(string $source, int $position, array &$table_rows): int
{
    $position_end = $position + \strcspn($source, "\r\n", $position);
    $line = \substr($source, $position, $position_end - $position);
    $cells = parse_table_row($line);
    if ($cells !== null) {
        // first non-separator row is the header (matches TableRule).
        $is_header = $table_rows === [];
        $table_rows[] = [$cells, $is_header];
    }
    return $position_end + \strspn($source, "\r\n", $position_end);
}

function consume_front_matter(
    string $source,
    int $position,
    int $length_bytes,
    array &$front_matter,
): int {
    $position += \strspn($source, '-', $position);
    $position += \strspn($source, "\r\n", $position);
    $close = \strpos($source, '---', $position);

    if ($close === false) {
        $content = \substr($source, $position);
        $position = $length_bytes;
    } else {
        $content = \substr($source, $position, $close - $position);
        $position = $close;
    }

    $position += \strspn($source, '-', $position);
    $position += \strspn($source, "\r\n", $position);

    try {
        $data = Yaml::parse($content);
    } catch (Throwable) {
        $data = null;
    }

    if (\is_array($data)) {
        $front_matter = [...$front_matter, ...$data];
    }

    return $position;
}

// collect `>`-prefixed lines, strip prefix, inline-render in QUOTE context.
function emit_blockquote(
    string $source,
    int $position,
    int $end,
    int $depth,
    string &$out,
): int {
    $content = '';
    while ($position < $end && $source[$position] === '>') {
        $line_end = $position + \strcspn($source, "\r\n", $position, $end - $position);
        $line = \substr($source, $position, $line_end - $position);
        if ($content !== '') {
            $content .= PHP_EOL;
        }
        $content .= \str_starts_with($line, '> ') ? \substr($line, 2) : \substr($line, 1);
        $position = $line_end + \strspn($source, "\r\n", $line_end, $end - $line_end);
    }

    $out .= '<blockquote>';
    emit_inline($content, 0, \strlen($content), InlineContext::QUOTE, $depth + 1, $out);
    $out .= '</blockquote>';

    return $position;
}

function consume_pre(
    string $source,
    int $position,
    int $length_bytes,
    string &$out,
): int {
    $position += 3;
    $line_end = $position + \strcspn($source, "\r\n", $position);
    $language = \substr($source, $position, $line_end - $position);
    $position = $line_end + \strspn($source, "\r\n", $line_end);

    $close = \strpos($source, '```', $position);
    if ($close === false) {
        $content = \substr($source, $position);
        $position = $length_bytes;
    } else {
        $content = \substr($source, $position, $close - $position);
        $position = $close + 3;
    }

    $position += \strspn($source, "\r\n", $position);
    $content = \trim($content);
    $language = $language !== '' ? $language : null;

    $out .= '<pre>';
    emit_code_tag($content, $language, $out);
    $out .= '</pre>';

    return $position;
}

function consume_div(
    string $source,
    int $position,
    int $length_bytes,
    string &$out,
): int {
    $position += \strspn($source, ':', $position);
    $line_end = $position + \strcspn($source, "\r\n", $position);
    $class = \substr($source, $position, $line_end - $position);
    $class = $class !== '' ? $class : null;
    $position = $line_end + \strspn($source, "\r\n", $line_end);

    $close = \strpos($source, ':::', $position);
    if ($close === false) {
        $content_start = $position;
        $content_end = $length_bytes;
        $position = $length_bytes;
    } else {
        $content_start = $position;
        $content_end = $close;
        $position = $close;
    }

    $position += \strspn($source, ':', $position);
    $position += \strspn($source, "\r\n", $position);

    $class_attr = $class !== null ? " class=\"{$class}\"" : '';
    $out .= "<div{$class_attr}>";
    emit_inline($source, $content_start, $content_end, InlineContext::DIV, 0, $out);
    $out .= '</div>';

    return $position;
}

function consume_html(string $source, int $position, int $length_bytes, string &$out): int
{
    $opening = '';
    $position = consume_tag($source, $position, $length_bytes, $opening);

    // no `>` in the rest of input: opening is the trailing bytes, emit and done.
    if (! \str_ends_with($opening, '>')) {
        $out .= $opening;
        return $position;
    }

    if (\str_ends_with($opening, '/>')) {
        $newline_end = $position + \strspn($source, "\r\n", $position);
        $out .= $opening . \substr($source, $position, $newline_end - $position);
        return $newline_end;
    }

    // leading word of the opening tag: "<div class..." -> "div".
    $tag_name_length = \strcspn($opening, " \t\n\r/>", 1);
    $tag_name = \substr($opening, 1, $tag_name_length);
    $open_match = "<{$tag_name}";
    $close_match = "</{$tag_name}";
    $open_length = \strlen($open_match);
    $close_length = \strlen($close_match);

    $out .= $opening;
    $depth = 1;
    $tag = '';

    while ($depth > 0 && $position < $length_bytes) {
        $next_tag_open = \strpos($source, '<', $position);
        if ($next_tag_open === false) {
            $out .= \substr($source, $position);
            $position = $length_bytes;
            break;
        }
        $out .= \substr($source, $position, $next_tag_open - $position);
        $position = $next_tag_open;

        if (\substr_compare($source, $close_match, $position, $close_length) === 0) {
            $depth--;
            $position = consume_tag($source, $position, $length_bytes, $tag);
            $out .= $tag;
        } elseif (\substr_compare($source, $open_match, $position, $open_length) === 0) {
            $depth++;
            $position = consume_tag($source, $position, $length_bytes, $tag);
            $out .= $tag;
        } else {
            // unrelated `<`, consume and keep scanning.
            $out .= '<';
            $position++;
        }
    }

    $newline_end = $position + \strspn($source, "\r\n", $position);
    $out .= \substr($source, $position, $newline_end - $position);

    return $newline_end;
}

// consume up to and including the next `>`; caller checks $tag_out ends in `>`.
function consume_tag(string $source, int $position, int $length_bytes, string &$tag_out): int
{
    $tag_close = \strpos($source, '>', $position);
    if ($tag_close === false) {
        $tag_out = \substr($source, $position);
        return $length_bytes;
    }
    $tag_out = \substr($source, $position, $tag_close - $position + 1);
    return $tag_close + 1;
}

//
// tables.
//

// trimmed cells, or null for a separator row.
function parse_table_row(string $line): ?array
{
    $cells = [];
    foreach (\explode('|', \trim($line, '| ')) as $cell) {
        $trimmed = \trim($cell);
        if ($trimmed !== '') {
            $cells[] = $trimmed;
        }
    }

    if ($cells === []) {
        return null;
    }

    foreach ($cells as $cell) {
        if (\trim($cell, '-: ') !== '') {
            return $cells;
        }
    }

    return null;
}

function render_table(array $table_rows, string &$out): void
{
    $headers = [];
    $bodies = [];
    foreach ($table_rows as [$cells, $is_header]) {
        if ($is_header) {
            $headers[] = $cells;
        } else {
            $bodies[] = $cells;
        }
    }

    $out .= '<table>';

    if ($headers !== []) {
        $out .= '<thead>';
        render_table_rows($headers, 'th', $out);
        $out .= '</thead>';
    }

    if ($bodies !== []) {
        $out .= '<tbody>';
        render_table_rows($bodies, 'td', $out);
        $out .= '</tbody>';
    }

    $out .= '</table>';
}

function render_table_rows(array $rows, string $cell_tag, string &$out): void
{
    foreach ($rows as $cells) {
        $out .= '<tr>';
        foreach ($cells as $cell) {
            $out .= "<{$cell_tag}>";
            emit_inline($cell, 0, \strlen($cell), InlineContext::TABLE, 0, $out);
            $out .= "</{$cell_tag}>";
        }
        $out .= '</tr>';
    }
}

//
// inline dispatcher and emitters.
//

// <code> with optional language-X class; block contexts wrap in <pre> at the call site.
function emit_code_tag(string $content, ?string $language, string &$out): void
{
    $highlighter = ParserState::$highlighter;

    if ($language === null && $highlighter !== null) {
        $language = $highlighter->fallbackLanguage?->getName();
    }

    if ($highlighter !== null) {
        $content = $highlighter->parse($content, $language);
    }

    $class = $language !== null ? " class=\"language-{$language}\"" : '';
    $out .= "<code{$class}>{$content}</code>";
}

// emit html for slice [$start, $end) of $content.
// add an inline rule: STOP_CHARS entry + match arm below + emit_X helper.
function emit_inline(
    string $content,
    int $start,
    int $end,
    int $context,
    int $depth,
    string &$out,
): void {
    \assert($depth < InlineContext::DEPTH_MAX);
    \assert($start >= 0 && $end >= $start);

    $stop = InlineContext::STOP_CHARS[$context];

    $position = $start;

    while ($position < $end) {
        $run = \strcspn($content, $stop, $position, $end - $position);
        if ($run > 0) {
            $out .= \substr($content, $position, $run);
            $position += $run;
            if ($position >= $end) {
                break;
            }
        }

        $char = $content[$position];

        // bare `!` not followed by `[` is literal text; bail keeps the match one-arm-per-char.
        if ($char === '!' && ($position + 1 >= $end || $content[$position + 1] !== '[')) {
            $out .= '!';
            $position++;
            continue;
        }

        $position = match ($char) {
            '*' => emit_paired($content, $position, $end, '*', 'strong', InlineContext::BOLD, $depth, $out),
            '_' => emit_paired($content, $position, $end, '_', 'em', InlineContext::ITALIC, $depth, $out),
            '~' => emit_paired($content, $position, $end, '~', 's', InlineContext::STRIKE, $depth, $out),
            '[' => emit_link($content, $position, $end, $depth, $out),
            '!' => emit_image($content, $position, $end, $out),
            '`' => emit_code_inline($content, $position, $end, $out),
            '>' => emit_blockquote($content, $position, $end, $depth, $out),
        };
    }
}

// same-marker emphasis: skip leading run, capture inner, skip trailing run, recurse.
function emit_paired(
    string $content,
    int $position,
    int $end,
    string $marker,
    string $tag,
    int $inner_context,
    int $depth,
    string &$out,
): int {
    $position += \strspn($content, $marker, $position, $end - $position);
    $inner_start = $position;
    $position += \strcspn($content, $marker, $position, $end - $position);
    $inner_end = $position;
    $position += \strspn($content, $marker, $position, $end - $position);

    $out .= "<{$tag}>";
    emit_inline($content, $inner_start, $inner_end, $inner_context, $depth + 1, $out);
    $out .= "</{$tag}>";

    return $position;
}

function emit_link(
    string $content,
    int $position,
    int $end,
    int $depth,
    string &$out,
): int {
    $position++; // skip '['
    $inner_start = $position;
    $position += \strcspn($content, ']', $position, $end - $position);
    $inner_end = $position;
    if ($position < $end && $content[$position] === ']') {
        $position++;
    }

    $href = null;
    if ($position < $end && $content[$position] === '(') {
        $position++;
        $href_run = \strcspn($content, ')', $position, $end - $position);
        $href = \substr($content, $position, $href_run);
        $position += $href_run;
        if ($position < $end && $content[$position] === ')') {
            $position++;
        }
    }

    // `*` prefix on href opens in a new tab (matches LinkToken).
    $target_attr = '';
    if ($href !== null && \str_starts_with($href, '*')) {
        $href = \substr($href, 1);
        $target_attr = ' target="_blank" rel="noopener noreferrer"';
    }
    $href_attr = $href ?? '';

    $out .= "<a href=\"{$href_attr}\"{$target_attr}>";
    emit_inline($content, $inner_start, $inner_end, InlineContext::LINK, $depth + 1, $out);
    $out .= '</a>';

    return $position;
}

function emit_image(string $content, int $position, int $end, string &$out): int
{
    $position += 2; // skip '!['
    $alt_run = \strcspn($content, ']', $position, $end - $position);
    $alt = $alt_run > 0 ? \substr($content, $position, $alt_run) : null;
    $position += $alt_run;
    if ($position < $end && $content[$position] === ']') {
        $position++;
    }

    $href = '';
    if ($position < $end && $content[$position] === '(') {
        $position++;
        $href_run = \strcspn($content, ')', $position, $end - $position);
        $href = \substr($content, $position, $href_run);
        $position += $href_run;
        if ($position < $end && $content[$position] === ')') {
            $position++;
        }
    }

    $alt_attr = $alt !== null ? " alt=\"{$alt}\"" : '';
    $out .= "<img src=\"{$href}\"{$alt_attr}>";

    return $position;
}

function emit_code_inline(string $content, int $position, int $end, string &$out): int
{
    $position++; // skip opening '`'

    // optional `{lang}` info string after the opening backtick:
    $language = null;
    if ($position < $end && $content[$position] === '{') {
        $position++;
        $brace_run = \strcspn($content, '}', $position, $end - $position);
        $language = \substr($content, $position, $brace_run);
        $position += $brace_run;
        if ($position < $end && $content[$position] === '}') {
            $position++;
        }
    }

    $code_run = \strcspn($content, '`', $position, $end - $position);
    $code = \substr($content, $position, $code_run);
    $position += $code_run;
    if ($position < $end && $content[$position] === '`') {
        $position++;
    }

    emit_code_tag($code, $language, $out);

    return $position;
}
