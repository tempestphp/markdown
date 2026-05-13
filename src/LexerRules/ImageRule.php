<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ImageToken;

final class ImageRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '!';

    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('![', 2);
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('![');
        $alt = $lexer->consumeUntil(']') ?: null;
        $lexer->consumeIncluding(']');

        if (! $lexer->comesNext('(', 1)) {
            // TODO: throw error
        }

        $lexer->consumeIncluding('(');
        $href = $lexer->consumeUntil(')');
        $lexer->consumeIncluding(')');

        return new ImageToken($href, $alt);
    }
}
