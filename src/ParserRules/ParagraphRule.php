<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class ParagraphRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return true;
    }

    public function parse(Parser $parser): string
    {
        $content = $parser->consumeUntil(Parser::NEW_LINE) . $parser->consumeWhile(Parser::NEW_LINE);

        $inner = $parser
            ->withRules(
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new ImageRule(),
                new CodeRule(),
                new TextRule(),
            )
            ->parse($content);

        return "<p>{$inner}</p>";
    }
}
