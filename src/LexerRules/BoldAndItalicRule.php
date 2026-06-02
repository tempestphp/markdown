<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldAndItalicToken;

final class BoldAndItalicRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_*';

    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('***', length: 3) || $lexer->comesNext('___', length: 3);
    }

    public function lex(Lexer $lexer): Token
    {
        $startToken = $lexer->consume(length: 3);
        $buffer = $lexer->consumeUntil($startToken[0]);
        $lexer->consume(length: 3);

        return new BoldAndItalicToken($buffer);
    }
}
