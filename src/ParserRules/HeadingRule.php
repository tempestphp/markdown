<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HeadingToken;

final readonly class HeadingRule implements Rule, ProvidesFirstChar
{
    public function __construct(
        public string $firstChar = '#',
    ) {}

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('#', 1);
    }

    public function parse(Parser $parser): Token
    {
        $buffer = $parser->consumeUntil(Parser::NEW_LINE);

        $level = strspn($buffer, '#');

        return new HeadingToken(substr($buffer, $level) |> trim(...), $level);
    }
}
