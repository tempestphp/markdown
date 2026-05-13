<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldToken;

final class BoldRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '*';

    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('*', 1);
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile('*');
        $buffer .= $lexer->consumeUntil('*');
        $buffer .= $lexer->consumeWhile('*');

        return new BoldToken(trim($buffer, '*'));
    }
}
