<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HtmlCommentToken;

final class HtmlCommentRule implements Rule, ProvidesFirstChar
{
    public string $firstChar = '<';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('<!--', length: 4);
    }

    public function parse(Parser $parser): Token
    {
        // consumeUntil() takes a set of characters, so it stops on the first
        // `-` in the body; a comment ends at the `-->` string.
        $buffer = $parser->consume(4);
        $buffer .= $parser->consumeUntilString('-->');
        $buffer .= $parser->consume(3);

        return new HtmlCommentToken($buffer);
    }
}
