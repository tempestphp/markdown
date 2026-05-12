<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldToken;

final readonly class BoldRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('*');
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile('*');
        $buffer .= $lexer->consumeUntil('*');
        $buffer .= $lexer->consumeWhile('*');

        return new BoldToken(trim($buffer, '*'));
    }
}
