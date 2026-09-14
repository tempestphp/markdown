<?php

namespace Tempest\Markdown;

/**
 * The `(destination "title")` part of an inline link or image.
 *
 * @see https://spec.commonmark.org/0.31.2/#link-destination
 * @see https://spec.commonmark.org/0.31.2/#link-title
 */
final readonly class InlineDestination
{
    private const string BARE_STOP_CHARS = '\\()' . Parser::WHITESPACE;

    private const string ANGLE_STOP_CHARS = '\\<>' . Parser::NEW_LINE;

    public function __construct(
        public string $destination,
        public ?string $title,

        /** How many characters the destination spans, closing parenthesis included. */
        public int $length,
    ) {}

    /**
     * Scans an inline destination starting at its opening parenthesis. Returns
     * `null` when it is malformed, in which case the surrounding construct is
     * not a link or an image and must be left as literal text.
     */
    public static function scan(string $content, int $position): ?self
    {
        $start = $position;

        if (($content[$position] ?? null) !== '(') {
            return null;
        }

        $position++;
        $position = self::skipWhitespace($content, $position);

        $destination = ($content[$position] ?? null) === '<'
            ? self::scanAngleDestination($content, $position)
            : self::scanBareDestination($content, $position);

        if ($destination === null) {
            return null;
        }

        [$destination, $position] = $destination;

        $beforeWhitespace = $position;
        $position = self::skipWhitespace($content, $position);

        $title = null;

        // A title has to be separated from the destination by whitespace,
        // otherwise `(a"b)` would be a destination followed by an open title.
        if ($position > $beforeWhitespace) {
            $scanned = self::scanTitle($content, $position);

            if ($scanned !== null) {
                [$title, $position] = $scanned;
                $position = self::skipWhitespace($content, $position);
            }
        }

        if (($content[$position] ?? null) !== ')') {
            return null;
        }

        return new self($destination, $title, $position + 1 - $start);
    }

    /** @return array{string, int}|null */
    private static function scanAngleDestination(
        string $content,
        int $position,
    ): ?array {
        $length = strlen($content);
        $destination = '';
        $position++;

        while ($position < $length) {
            $offset = strcspn($content, self::ANGLE_STOP_CHARS, $position);

            if ($offset > 0) {
                $destination .= substr($content, $position, $offset);
                $position += $offset;
            }

            $character = $content[$position] ?? null;

            if ($character === '\\' && isset($content[$position + 1])) {
                $destination .= $content[$position + 1];
                $position += 2;

                continue;
            }

            if ($character === '>') {
                return [self::decodeEntities($destination), $position + 1];
            }

            // An unescaped `<` or a line ending closes nothing and makes the
            // whole construct literal text.
            return null;
        }

        return null;
    }

    /** @return array{string, int}|null */
    private static function scanBareDestination(
        string $content,
        int $position,
    ): ?array {
        $length = strlen($content);
        $destination = '';
        $depth = 0;

        while ($position < $length) {
            // Bulk-skip to the next character that needs attention rather
            // than walking the destination one character at a time.
            $offset = strcspn($content, self::BARE_STOP_CHARS, $position);

            if ($offset > 0) {
                $destination .= substr($content, $position, $offset);
                $position += $offset;
            }

            $character = $content[$position] ?? null;

            if ($character === '\\' && isset($content[$position + 1])) {
                $destination .= $content[$position + 1];
                $position += 2;

                continue;
            }

            if ($character === '(') {
                $depth++;
            } elseif ($character === ')') {
                if ($depth === 0) {
                    break;
                }

                $depth--;
            } elseif ($character !== '\\') {
                // Whitespace, or the end of the content.
                break;
            }

            $destination .= $character;
            $position++;
        }

        if ($depth !== 0) {
            return null;
        }

        return [self::decodeEntities($destination), $position];
    }

    /** @return array{string, int}|null */
    private static function scanTitle(string $content, int $position): ?array
    {
        $opening = $content[$position] ?? null;

        $closing = match ($opening) {
            '"' => '"',
            "'" => "'",
            '(' => ')',
            default => null,
        };

        if ($closing === null) {
            return null;
        }

        $length = strlen($content);
        $stopChars = '\\' . $closing;
        $title = '';
        $position++;

        while ($position < $length) {
            $offset = strcspn($content, $stopChars, $position);

            if ($offset > 0) {
                $title .= substr($content, $position, $offset);
                $position += $offset;
            }

            $character = $content[$position] ?? null;

            if ($character === '\\' && isset($content[$position + 1])) {
                $title .= $content[$position + 1];
                $position += 2;

                continue;
            }

            if ($character === $closing) {
                return [self::decodeEntities($title), $position + 1];
            }

            break;
        }

        return null;
    }

    private static function skipWhitespace(string $content, int $position): int
    {
        return $position + strspn($content, Parser::WHITESPACE, $position);
    }

    private static function decodeEntities(string $value): string
    {
        return str_contains($value, '&')
            ? html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')
            : $value;
    }
}
