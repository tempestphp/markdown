<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TextToken;

final readonly class TextRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return true;
    }

    public function lex(Lexer $lexer): ?Token
    {
        if ($lexer->lastToken instanceof TextToken) {
            $lexer->lastToken->append($lexer->consume());
            return null;
        }

        return new TextToken($lexer->consume());
    }
}