<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\MarkdownLexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\NewLineToken;

final readonly class NewLineRule implements LexerRule
{
    public function matches(MarkdownLexer $lexer): bool
    {
        return $lexer->current === "\n" || $lexer->current === "\r";
    }

    public function lex(MarkdownLexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile(MarkdownLexer::NEW_LINE);

        return new NewLineToken($buffer);
    }
}