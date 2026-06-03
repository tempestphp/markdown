<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\PreToken;

final readonly class PreRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('```', 3);
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('```');

        $language = $lexer->consumeUntil(Lexer::WHITESPACE);

        $lexer->consumeUntil(Lexer::NEW_LINE);

        $lexer->consumeWhile(Lexer::NEW_LINE);

        $content = $lexer->consumeUntilString('```');

        $lexer->consumeIncluding('```');
        $lexer->consumeWhile(Lexer::NEW_LINE);

        // Remove trailing newline.
        if (str_ends_with($content, PHP_EOL)) {
            $content = substr($content, 0, -1);
        }

        return new PreToken(
            language: $language ?: null,
            content: $content,
        );
    }
}
