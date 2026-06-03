<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HtmlCommentToken;

final readonly class HtmlCommentRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('<!--', length: 4);
    }

    public function lex(Lexer $lexer): Token
    {
        $buffer = $lexer->consumeWhile('<!--');
        $buffer .= $lexer->consumeUntil('-->');
        $buffer .= $lexer->consumeWhile('-->');

        return new HtmlCommentToken($buffer);
    }
}
