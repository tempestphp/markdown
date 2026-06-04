<?php

namespace Tempest\Markdown\ParserRules;

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
        $buffer = $parser->consumeWhile('<!--');
        $buffer .= $parser->consumeUntil('-->');
        $buffer .= $parser->consumeWhile('-->');

        return new HtmlCommentToken($buffer);
    }
}
