<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\CodeToken;

final readonly class CodeRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('`');
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('`');

        $content = $lexer->consumeUntil('`');

        $lexer->consumeIncluding('`');

        return new CodeToken($content);
    }
}
