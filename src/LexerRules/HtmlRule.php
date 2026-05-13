<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HtmlToken;

final readonly class HtmlRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('<');
    }

    public function lex(Lexer $lexer): Token
    {
        $openingTag = $lexer->consumeIncluding('>');

        if (str_ends_with($openingTag, '/>')) {
            return new HtmlToken($openingTag . $lexer->consumeWhile(Lexer::NEW_LINE));
        }

        $content = $openingTag . $lexer->consumeUntil('</') . $lexer->consumeIncluding('>') . $lexer->consumeWhile(Lexer::NEW_LINE);

        return new HtmlToken($content);
    }
}
