<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;

final class LinkRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '[';

    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('[', 1);
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('[');
        $content = $lexer->consumeUntil(']');
        $lexer->consumeIncluding(']');

        $href = null;

        if ($lexer->comesNext('(', 1)) {
            $lexer->consumeIncluding('(');
            $href = $lexer->consumeUntil(')');
            $lexer->consumeIncluding(')');
        }

        return new LinkToken($content, $href);
    }
}
