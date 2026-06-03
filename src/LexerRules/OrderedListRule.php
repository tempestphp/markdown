<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\OrderedListToken;

final readonly class OrderedListRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        if (! ctype_digit($lexer->current ?? '')) {
            return false;
        }

        $search = $lexer->lookaheadUntil('.', PHP_EOL);

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

    public function lex(Lexer $lexer): ?Token
    {
        $lexer->consumeWhile('0123456789');
        $lexer->consumeIncluding('.');
        $content = trim($lexer->consumeUntil(Lexer::NEW_LINE));
        $lexer->consumeWhile(Lexer::NEW_LINE);

        $childContent = '';
        $indent = strspn($lexer->content, ' ', $lexer->position);

        while ($indent >= 2 && $lexer->current !== null) {
            if (strspn($lexer->content, ' ', $lexer->position) < $indent) {
                break;
            }

            $lexer->consume($indent);
            $childContent .= $lexer->consumeUntil(Lexer::NEW_LINE) . PHP_EOL;
            $lexer->consumeWhile(Lexer::NEW_LINE);
        }

        $children = $childContent !== ''
            ? new Lexer([new OrderedListRule()])->lex($childContent)[0]
            : null;

        $item = new ListItem($content, $children);

        if ($lexer->lastToken instanceof OrderedListToken) {
            $lexer->lastToken->items[] = $item;
            return null;
        }

        return new OrderedListToken([$item]);
    }
}
