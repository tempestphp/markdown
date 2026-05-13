<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TableRow;
use Tempest\Markdown\Tokens\TableToken;

final readonly class TableRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('|', 1);
    }

    public function lex(Lexer $lexer): ?Token
    {
        $line = $lexer->consumeUntil(Lexer::NEW_LINE);
        $lexer->consumeWhile(Lexer::NEW_LINE);

        $cells = $line
            |> (fn ($x) => trim($x, '| '))
            |> (fn ($x) => explode('|', $x))
            |> (fn ($x) => array_map(trim(...), $x))
            |> (fn ($x) => array_filter($x, fn (string $cell) => $cell !== ''))
            |> array_values(...);

        // Filter out separator rows
        $isSeparator = array_filter($cells, fn (string $cell) => trim($cell, '-: ') !== '') === [];

        if ($isSeparator) {
            return null;
        }

        $isHeader = ! $lexer->lastToken instanceof TableToken;

        $row = new TableRow($cells, $isHeader);

        if ($lexer->lastToken instanceof TableToken) {
            $lexer->lastToken->rows[] = $row;
            return null;
        }

        return new TableToken([$row]);
    }
}
