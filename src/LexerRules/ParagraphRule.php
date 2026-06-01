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
        $content = '';

        while ($lexer->current !== null) {
            $content .= $lexer->consumeUntil(Lexer::NEW_LINE);

            if ($lexer->current === null) {
                break;
            }

            // A blank line (two consecutive newlines) ends the paragraph
            if ($lexer->comesNext("\n\n", 2) || $lexer->comesNext("\r\n\r\n", 4) || $lexer->comesNext("\n\r\n", 3) || $lexer->comesNext("\r\n\n", 3)) {
                break;
            }

            // Single newline — consume it and continue to the next line
            $content .= $lexer->consumeWhile(Lexer::NEW_LINE);
        }

        return new ParagraphToken($content);
    }
}
