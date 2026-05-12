<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ItalicToken;

final readonly class ItalicRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('_');
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile('_');
        $buffer .= $lexer->consumeUntil('_');
        $buffer .= $lexer->consumeWhile('_');

        return new ItalicToken(trim($buffer, '_'));
    }
}
