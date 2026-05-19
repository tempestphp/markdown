<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class NewLineRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->current === "\n" || $parser->current === "\r";
    }

    public function parse(Parser $parser): string
    {
        return $parser->consumeWhile(Parser::NEW_LINE);
    }
}
