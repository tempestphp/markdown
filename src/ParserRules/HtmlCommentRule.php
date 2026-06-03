<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HtmlCommentToken;

final readonly class HtmlCommentRule implements Rule
{
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
