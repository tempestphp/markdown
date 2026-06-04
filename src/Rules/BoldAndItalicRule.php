<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldAndItalicToken;

final class BoldAndItalicRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private(set) string $firstChar = '*_';
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
