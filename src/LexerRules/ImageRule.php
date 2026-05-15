<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
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
            throw new ImageSourceWasMissing($lexer);
        }

        $lexer->consumeIncluding('(');
        $href = $lexer->consumeUntil(')' . Lexer::NEW_LINE);

        if (! $lexer->comesNext(')')) {
            throw new ImageSourceWasNotClosed($lexer);
        }

        $lexer->consumeIncluding(')');

        return new ImageToken($href, $alt);
    }
}
