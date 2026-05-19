<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class OrderedListRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        if (! ctype_digit($parser->current ?? '')) {
            return false;
        }

        $search = $parser->lookaheadUntil('.', PHP_EOL);

        if (count($search) !== 2) {
            return false;
        }

        return true;
    }

    public function parse(Parser $parser): string
    {
        $items = [];

        while ($this->shouldParse($parser)) {
            $parser->consumeWhile('0123456789');
            $parser->consumeIncluding('.');
            $content = trim($parser->consumeUntil(Parser::NEW_LINE));
            $parser->consumeWhile(Parser::NEW_LINE);

            $childContent = '';

            while ($parser->comesNext('  ', 2)) {
                $parser->consume(2); // strip one indent level
                $childContent .= $parser->consumeUntil(Parser::NEW_LINE) . "\n";
                $parser->consumeWhile(Parser::NEW_LINE);
            }

            $children = $childContent !== ''
                ? (string) new Parser([new OrderedListRule()])->parse($childContent)
                : '';

            $items[] = [$content, $children];
        }

        $inlineParser = $parser->withRules(
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule(),
        );

        $list = '<ol>';

        foreach ($items as [$content, $children]) {
            $parsed = $inlineParser->parse($content);
            $list .= "<li>{$parsed}{$children}</li>";
        }

        $list .= '</ol>';

        return $list;
    }
}
