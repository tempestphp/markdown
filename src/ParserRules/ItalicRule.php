<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ItalicToken;

final class ItalicRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('_', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeWhile('_');
        $buffer = $parser->consumeUntil('_');
        $parser->consumeWhile('_');

        return new ItalicToken(trim($buffer, '_'));
    }
}
