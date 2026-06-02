<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldToken;

final class BoldRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_*';

    public function shouldLex(Lexer $lexer): bool
    {
        if ($lexer->comesNext('**', length: 2)) {
            return ! $lexer->comesNext('*', length: 1, offset: 2);
        }

        if ($lexer->comesNext('__', length: 2)) {
            return ! $lexer->comesNext('_', length: 1, offset: 2);
        }

        return false;
    }

    public function lex(Lexer $lexer): Token
    {
        $startToken = $lexer->consume(length: 2);
        $buffer = $lexer->consumeUntil($startToken[0]);
        $lexer->consume(length: 2);

        return new BoldToken($buffer);
    }
}
