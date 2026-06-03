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
        if ($parser->comesNext('_', length: 1)) {
            return ! $parser->comesNext('_', length: 1, offset: 1);
        }

        if ($parser->comesNext('*', length: 1)) {
            return ! $parser->comesNext('*', length: 1, offset: 1);
        }

        return false;
    }

    public function parse(Parser $parser): Token
    {
        $startToken = $parser->consume();
        $buffer = $parser->consumeUntil($startToken);
        $parser->consume();

        return new ItalicToken($buffer);
    }
}
