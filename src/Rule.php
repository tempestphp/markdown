<?php

namespace Tempest\Markdown;

interface Rule
{
    public function shouldLex(Lexer $lexer): bool;

    public function lex(Lexer $lexer): ?Token;
}
