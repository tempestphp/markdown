<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\QuoteToken;

final readonly class QuoteRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('>');
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeUntil(Lexer::NEW_LINE);

        $level = strspn($buffer, '>');

        return new QuoteToken(substr($buffer, $level) |> trim(...), $level);
    }
}
