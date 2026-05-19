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
        return $lexer->comesNext('- ', 2);
    }

    public function lex(Lexer $lexer): Token
    {
        $items = [];

        while ($lexer->comesNext('- ', 2)) {
            $lexer->consumeIncluding('- ');
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

            $items[] = new ListItem($content, $children);
        }

        return new ListToken($items);
    }
}
