<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class ListRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('- ', 2);
    }

    public function parse(Parser $parser): string
    {
        $items = [];

        while ($parser->comesNext('- ', 2)) {
            $parser->consumeIncluding('- ');
            $content = trim($parser->consumeUntil(Parser::NEW_LINE));
            $parser->consumeWhile(Parser::NEW_LINE);

            $childContent = '';

            while ($parser->comesNext('  ', 2)) {
                $parser->consume(2); // strip one indent level
                $childContent .= $parser->consumeUntil(Parser::NEW_LINE) . PHP_EOL;
                $parser->consumeWhile(Parser::NEW_LINE);
            }

            $children = $childContent !== ''
                ? (string) new Parser([new ListRule()])->parse($childContent)
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

        $list = '<ul>';

        foreach ($items as [$content, $children]) {
            $parsed = $inlineParser->parse($content);
            $list .= "<li>{$parsed}{$children}</li>";
        }

        $list .= '</ul>';

        return $list;
    }
}
