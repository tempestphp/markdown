<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\StrikethroughToken;

final class StrikethroughRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private(set) string $firstChar = '~';
    private(set) string $stopChar = '~';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('~', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeWhile('~');
        $buffer = $parser->consumeUntil('~');
        $parser->consumeWhile('~');

        return new StrikethroughToken($buffer);
    }
}
