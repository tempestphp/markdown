<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class DivRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext(':::', 3);
    }

    public function parse(Parser $parser): string
    {
        $parser->consumeWhile(':');

        $class = $parser->consumeUntil(Parser::NEW_LINE) ?: null;

        $parser->consumeWhile(Parser::NEW_LINE);

        $content = $parser->consumeUntilString(':::');

        $parser->consumeWhile(':');
        $parser->consumeWhile(Parser::NEW_LINE);

        $inner = $parser
            ->withRules(
                new QuoteRule(),
                new BoldRule(),
                new ItalicRule(),
                new LinkRule(),
                new ImageRule(),
                new TextRule(),
            )
            ->parse($content);

        $classAttr = $class ? " class=\"{$class}\"" : '';

        return "<div{$classAttr}>{$inner}</div>";
    }
}
