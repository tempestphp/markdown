<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\NewLineToken;

final readonly class NewLineRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->current === "\n" || $lexer->current === "\r";
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile(Lexer::NEW_LINE);

        return new NewLineToken($buffer);
    }
}