<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\RawToken;

final readonly class RawRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('@@', length: 2);
    }

    public function parse(Parser $parser): ?Token
    {
        $parser->consume(2);
        $content = $parser->consumeUntil('@@');
        $parser->consume(2);

        return new RawToken($content);
    }
}