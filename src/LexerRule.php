<?php

namespace Tempest\Markdown;

interface LexerRule
{
    public function matches(MarkdownLexer $lexer): bool;

    public function lex(MarkdownLexer $lexer): Token;
}