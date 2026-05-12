<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ParagraphToken;

final readonly class ParagraphRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return true;
    }

    public function lex(Lexer $lexer): Token
    {
        $content = $lexer->consumeIncluding(Lexer::NEW_LINE);

        return new ParagraphToken($content);
    }
}
