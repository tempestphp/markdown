<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldToken;

final class BoldRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '*';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('*', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeWhile('*');
        $buffer = $parser->consumeUntil('*');
        $parser->consumeWhile('*');

        return new BoldToken($buffer);
    }
}
