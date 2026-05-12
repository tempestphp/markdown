<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HeadingToken;

final readonly class HeadingRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('#', 1);
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeUntil(Lexer::NEW_LINE);

        $level = strspn($buffer, '#');

        return new HeadingToken(substr($buffer, $level) |> trim(...), $level);
    }
}
