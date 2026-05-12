<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\PreToken;

final readonly class PreRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('```');
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('```');

        $language = $lexer->consumeUntil(Lexer::NEW_LINE);

        $lexer->consumeWhile(Lexer::NEW_LINE);

        $content = $lexer->consumeUntil('```');

        $lexer->consumeIncluding('```');
        $lexer->consumeWhile(Lexer::NEW_LINE);

        return new PreToken(
            language: $language ?: null,
            content: trim($content),
        );
    }
}
