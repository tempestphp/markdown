<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\QuoteToken;

final class QuoteRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '>';

    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('>', 1);
    }

    public function lex(Lexer $lexer): Token
    {
        $lines = [];

        while ($lexer->comesNext('>', 1)) {
            $line = $lexer->consumeUntil(Lexer::NEW_LINE);

            if (str_starts_with($line, '> ')) {
                $line = substr($line, 2);
            } else {
                $line = substr($line, 1);
            }

            $lines[] = $line;

            $lexer->consumeWhile(Lexer::NEW_LINE);
        }

        $content = implode(PHP_EOL, $lines);

        return new QuoteToken($content);
    }
}
