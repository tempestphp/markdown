<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\ListToken;

final readonly class ListRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('-', 1);
    }

    public function lex(Lexer $lexer): ?Token
    {
        $lexer->consumeIncluding('-');
        $content = trim($lexer->consumeUntil(Lexer::NEW_LINE));
        $lexer->consumeWhile(Lexer::NEW_LINE);

        $childContent = '';

        while ($lexer->comesNext('  ', 2)) {
            $lexer->consume(2); // strip one indent level
            $childContent .= $lexer->consumeUntil(Lexer::NEW_LINE) . PHP_EOL;
            $lexer->consumeWhile(Lexer::NEW_LINE);
        }

        $children = $childContent !== ''
            ? new Lexer([new ListRule()])->lex($childContent)[0]
            : null;

        $item = new ListItem($content, $children);

        if ($lexer->lastToken instanceof ListToken) {
            $lexer->lastToken->items[] = $item;
            return null;
        }

        return new ListToken([$item]);
    }
}
