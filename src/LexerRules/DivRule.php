<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\DivToken;

final readonly class DivRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext(':::', 3);
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeWhile(':');

        $class = $lexer->consumeUntil(Lexer::NEW_LINE) ?: null;

        $lexer->consumeWhile(Lexer::NEW_LINE);

        $content = $lexer->consumeUntilString(':::');

        $lexer->consumeWhile(':');
        $lexer->consumeWhile(Lexer::NEW_LINE);

        return new DivToken(
            class: $class,
            content: $content,
        );
    }
}
