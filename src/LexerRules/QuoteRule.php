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
        if (! $lexer->comesNext('>', 1)) {
            return false;
        }

        return $lexer->position === 0 || ($lexer->content[$lexer->position - 1] ?? null) === PHP_EOL;
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
