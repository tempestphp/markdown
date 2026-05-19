<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;

final class ItalicRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '_';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('_', 1);
    }

    public function parse(Parser $parser): string
    {
        $parser->consumeWhile('_');
        $buffer = $parser->consumeUntil('_');
        $parser->consumeWhile('_');

        $content = $parser
            ->withRules(
                new BoldRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse(trim($buffer, '_'));

        return "<em>{$content}</em>";
    }
}
