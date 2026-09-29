<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TextToken;

/**
 * A backslash before ASCII punctuation makes that character literal.
 *
 * @see https://spec.commonmark.org/0.31.2/#backslash-escapes
 */
final class EscapeRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private const string PUNCTUATION = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

    private(set) string $firstChar = '\\';
    private(set) string $stopChar = '\\';

    public function shouldParse(Parser $parser): bool
    {
        $next = $parser->content[$parser->position + 1] ?? '';

        return $next !== '' && str_contains(self::PUNCTUATION, $next);
    }

    public function parse(Parser $parser): ?Token
    {
        $parser->consume();
        $char = $parser->consume();

        // Written as an entity before a line ending, an escaped backslash
        // cannot be read back as a hard line break by TextToken.
        $text = $char === '\\'
        && str_contains(Parser::NEW_LINE, $parser->current ?? ' ')
            ? '&#92;'
            : htmlspecialchars($char, ENT_COMPAT | ENT_HTML5);

        if ($parser->lastToken instanceof TextToken) {
            $parser->lastToken->append($text);

            return null;
        }

        return new TextToken($text);
    }
}
