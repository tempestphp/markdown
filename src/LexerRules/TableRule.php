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
        if (! $lexer->comesNext('|', 1)) {
            return false;
        }

        if ($lexer->lastToken instanceof TableToken) {
            return true;
        }

        $nextTwoLines = $lexer->lookaheadUntil(Lexer::NEW_LINE, Lexer::NEW_LINE);

        if (count($nextTwoLines) !== 2) {
            return false;
        }

        // The second line must be a separator and only contain these characters: `:| -\r\n`
        if (trim($nextTwoLines[1], ":| -\r\n") !== '') {
            return false;
        }

        return true;
    }

    public function lex(Lexer $lexer): ?Token
    {
        $line = $lexer->consumeUntil(Lexer::NEW_LINE);
        $lexer->consumeWhile(Lexer::NEW_LINE);

        // Filter out separator rows
        $isSeparator = trim($line, ':| -') === '';

        if ($isSeparator) {
            // TODO: determine alignment based on the separator
            return null;
        }

        // Create cells
        $cells = $line
            |> (fn ($x) => trim($x, '| '))
            |> (fn ($x) => explode('|', $x))
            |> (fn ($x) => array_map(trim(...), $x))
            |> (fn ($x) => array_filter($x, fn (string $cell) => $cell !== ''))
            |> array_values(...);

        // Determine if is header row
        $token = $lexer->lastToken;
        $isHeader = ! $token instanceof TableToken;

        $row = new TableRow($cells, $isHeader);

        if ($isHeader) {
            return new TableToken([$row]);
        }

        /** @var TableToken $token */
        $token->rows[] = $row;

        return null;
    }
}
