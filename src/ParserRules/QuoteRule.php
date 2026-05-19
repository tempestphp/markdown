<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\QuoteToken;

final class QuoteRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '>';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('>', 1);
    }

    public function parse(Parser $parser): Token
    {
        $lines = [];

        while ($parser->comesNext('>', 1)) {
            $line = $parser->consumeUntil(Parser::NEW_LINE);

            if (str_starts_with($line, '> ')) {
                $line = substr($line, 2);
            } else {
                $line = substr($line, 1);
            }

            $lines[] = $line;

            $parser->consumeWhile(Parser::NEW_LINE);
        }

        $content = implode(PHP_EOL, $lines);

        return new QuoteToken($content);
    }
}
