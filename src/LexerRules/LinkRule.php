<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;

final readonly class LinkRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('[');
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('[');
        $content = $lexer->consumeUntil(']');
        $lexer->consumeIncluding(']');

        $href = null;

        if ($lexer->comesNext('(')) {
            $lexer->consumeIncluding('(');
            $href = $lexer->consumeUntil(')');
            $lexer->consumeIncluding(')');
        }

        return new LinkToken($content, $href);
    }
}
