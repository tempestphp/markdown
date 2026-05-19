<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;

final class BoldRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '*';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('*', 1);
    }

    public function parse(Parser $parser): string
    {
        $parser->consumeWhile('*');
        $buffer = $parser->consumeUntil('*');
        $parser->consumeWhile('*');

        $content = $parser
            ->withRules(
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse($buffer);

        return "<strong>{$content}</strong>";
    }
}
