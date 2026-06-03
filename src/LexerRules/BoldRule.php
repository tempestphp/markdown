<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldToken;

final class BoldRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_*';

    public function shouldParse(Parser $parser): bool
    {
        if ($parser->comesNext('**', length: 2)) {
            return ! $parser->comesNext('*', length: 1, offset: 2);
        }

        if ($parser->comesNext('__', length: 2)) {
            return ! $parser->comesNext('_', length: 1, offset: 2);
        }

        return false;
    }

    public function parse(Parser $parser): Token
    {
        $startToken = $parser->consume(length: 2);
        $buffer = $parser->consumeUntil($startToken[0]);
        $parser->consume(length: 2);

        return new BoldToken($buffer);
    }
}
