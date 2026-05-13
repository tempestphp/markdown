<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ListToken;

final readonly class ListRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('-', 1);
    }

    public function lex(Lexer $lexer): ?Token
    {
        $lexer->consumeIncluding('-');
        $content = $lexer->consumeIncluding(Lexer::NEW_LINE);

        if ($lexer->lastToken instanceof ListToken) {
            $lexer->lastToken->items[] = trim($content);
            return null;
        }

        return new ListToken([trim($content)]);
    }
}
