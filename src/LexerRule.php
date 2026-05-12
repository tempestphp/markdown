<?php

namespace Tempest\Markdown;

interface LexerRule
{
    public function shouldLex(Lexer $lexer): bool;

    public function lex(Lexer $lexer): ?Token;
}