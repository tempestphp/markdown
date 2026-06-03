<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldAndItalicToken;

final class BoldAndItalicRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_*';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('***', length: 3) || $parser->comesNext('___', length: 3);
    }

    public function parse(Parser $parser): Token
    {
        $startToken = $parser->consume(length: 3);
        $buffer = $parser->consumeUntil($startToken[0]);
        $parser->consume(length: 3);

        return new BoldAndItalicToken($buffer);
    }
}
