<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\NewLineToken;

final class NewLineRule implements Rule, ProvidesFirstChar
{
    public string $firstChar = "\n\r";

    public function shouldParse(Parser $parser): bool
    {
        return $parser->current === "\n" || $parser->current === "\r";
    }

    public function parse(Parser $parser): Token
    {
        $buffer = $parser->consumeWhile(Parser::NEW_LINE);

        return new NewLineToken($buffer);
    }
}
