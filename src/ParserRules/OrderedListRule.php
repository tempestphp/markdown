<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\OrderedListToken;

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

        if (! ctype_digit(rtrim($search[0], '.'))) {
            return false;
        }

        // Must be followed by a space.
        if (($search[1][0] ?? null) !== ' ') {
            return false;
        }

        return true;
    }

    public function parse(Parser $parser): ?Token
    {
        $parser->consumeWhile('0123456789');
        $parser->consumeIncluding('.');
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
            ? $parser->withRules(new OrderedListRule())->lex($childContent)[0]
            : null;

        $item = new ListItem($content, $children);

        if ($parser->lastToken instanceof OrderedListToken) {
            $parser->lastToken->items[] = $item;
            return null;
        }

        return new OrderedListToken([$item]);
    }
}
