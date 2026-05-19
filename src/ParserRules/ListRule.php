<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\ListToken;

final readonly class ListRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('- ', 2);
    }

    public function parse(Parser $parser): Token
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
                : null;

            $items[] = new ListItem($content, $children);
        }

        return new ListToken($items);
    }
}
