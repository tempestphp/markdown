<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ImageToken;

final readonly class ImageRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('![');
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('![');
        $alt = $lexer->consumeUntil(']') ?: null;
        $lexer->consumeIncluding(']');

        if (! $lexer->comesNext('(')) {
            // TODO: throw error
        }

        $lexer->consumeIncluding('(');
        $href = $lexer->consumeUntil(')');
        $lexer->consumeIncluding(')');

        return new ImageToken($href, $alt);
    }
}
