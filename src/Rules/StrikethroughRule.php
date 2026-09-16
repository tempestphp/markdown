<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\StrikethroughToken;
use Tempest\Markdown\Tokens\TextToken;

final class StrikethroughRule implements
    Rule,
    ProvidesFirstChar,
    ProvidesStopChar
{
    private(set) string $firstChar = '~';
    private(set) string $stopChar = '~';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('~', 1);
    }

    public function parse(Parser $parser): Token
    {
        $opening = $parser->consumeWhile('~');

        $closing = $this->closingPosition($parser, strlen($opening));

        if ($closing === null) {
            return new TextToken($opening);
        }

        $buffer = $parser->consume($closing - $parser->position);

        $parser->consume(strlen($opening));

        return new StrikethroughToken($buffer);
    }

    /**
     * The position of the run that closes $length tildes, or null when there
     * is none. A run of more than two tildes never delimits strikethrough, an
     * opening run may not be followed by whitespace, and a closing run may not
     * be preceded by it.
     *
     * @see https://github.github.com/gfm/#strikethrough-extension-
     */
    private function closingPosition(Parser $parser, int $length): ?int
    {
        if ($length > 2) {
            return null;
        }

        $next = $parser->current;

        if ($next === null || str_contains(Parser::WHITESPACE, $next)) {
            return null;
        }

        $content = $parser->content;
        $position = $parser->position;

        while (($position = strpos($content, '~', $position)) !== false) {
            $run = strspn($content, '~', $position);

            if (
                $run === $length
                && ! str_contains(Parser::WHITESPACE, $content[$position - 1])
            ) {
                return $position;
            }

            $position += $run;
        }

        return null;
    }
}
