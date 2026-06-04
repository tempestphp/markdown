<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ParagraphToken;

final readonly class ParagraphRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return true;
    }

    public function parse(Parser $parser): Token
    {
        $content = '';

        while ($parser->current !== null) {
            $content .= $parser->consumeUntil(Parser::NEW_LINE);

            if ($parser->current === null) {
                break;
            }

            // A blank line (two consecutive newlines) ends the paragraph
            if ($parser->comesNext("\n\n", 2) || $parser->comesNext("\r\n\r\n", 4) || $parser->comesNext("\n\r\n", 3) || $parser->comesNext("\r\n\n", 3)) {
                break;
            }

            // Single newline — consume it and continue to the next line
            $content .= $parser->consumeWhile(Parser::NEW_LINE);
        }

        return new ParagraphToken($content);
    }
}
