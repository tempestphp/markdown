<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\CodeToken;

final readonly class CodeRule implements Rule
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
