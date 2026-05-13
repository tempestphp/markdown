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
        return $lexer->comesNext('_', 1);
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile('_');
        $buffer .= $lexer->consumeUntil('_');
        $buffer .= $lexer->consumeWhile('_');

        return new ItalicToken(trim($buffer, '_'));
    }
}
