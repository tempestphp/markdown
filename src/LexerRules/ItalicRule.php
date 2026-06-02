<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ItalicToken;

final class ItalicRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_';

    public function shouldLex(Lexer $lexer): bool
    {
        if ($lexer->comesNext('_', length: 1)) {
            return ! $lexer->comesNext('_', length: 1, offset: 1);
        }

        if ($lexer->comesNext('*', length: 1)) {
            return ! $lexer->comesNext('*', length: 1, offset: 1);
        }

        return false;
    }

    public function lex(Lexer $lexer): Token
    {
        $startToken = $lexer->consume();
        $buffer = $lexer->consumeUntil($startToken);
        $lexer->consume();

        return new ItalicToken($buffer);
    }
}
