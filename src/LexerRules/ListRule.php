<?php

namespace Tempest\Markdown\LexerRules;

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

    public function parse(Parser $parser): ?Token
    {
        $parser->consumeIncluding('- ');
        $content = trim($parser->consumeUntil(Parser::NEW_LINE));
        $parser->consumeWhile(Parser::NEW_LINE);

        $childContent = '';
        $indent = strspn($parser->content, ' ', $parser->position);

        while ($indent >= 2 && $parser->current !== null) {
            if (strspn($parser->content, ' ', $parser->position) < $indent) {
                break;
            }

            $parser->consume($indent);
            $childContent .= $parser->consumeUntil(Parser::NEW_LINE) . PHP_EOL;
            $parser->consumeWhile(Parser::NEW_LINE);
        }

        $children = $childContent !== ''
            ? $parser->withRules(new ListRule())->lex($childContent)[0]
            : null;

        $item = new ListItem($content, $children);

        if ($parser->lastToken instanceof ListToken) {
            $parser->lastToken->items[] = $item;
            return null;
        }

        return new ListToken([$item]);
    }
}
