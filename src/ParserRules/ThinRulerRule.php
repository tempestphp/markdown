<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class ThinRulerRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('---', 3);
    }

    public function parse(Parser $parser): string
    {
        $parser->consumeWhile('-');

        return '<hr/>';
    }
}
