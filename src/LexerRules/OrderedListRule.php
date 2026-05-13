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
        return ctype_digit($lexer->current ?? '');
    }

    public function lex(Lexer $lexer): ?Token
    {
        $lexer->consumeWhile('0123456789');
        $lexer->consumeIncluding('.');
        $content = trim($lexer->consumeUntil(Lexer::NEW_LINE));
        $lexer->consumeWhile(Lexer::NEW_LINE);

        $childContent = '';

        while ($lexer->comesNext('  ', 2)) {
            $lexer->consume(2); // strip one indent level
            $childContent .= $lexer->consumeUntil(Lexer::NEW_LINE) . "\n";
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
