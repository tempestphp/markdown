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

        $nextTwoLines = $lexer->lookaheadUntil(Lexer::NEW_LINE, Lexer::NEW_LINE);

        if (count($nextTwoLines) !== 2) {
            return false;
        }

        // The second line must be a separator and only contain these characters: `:| -\r\n`
        if (trim($nextTwoLines[1], ":| -\r\n") !== '') {
            return false;
        }

        // The separator line MUST contain at least one dash
        if (! str_contains($nextTwoLines[1], '-')) {
            return false;
        }

        return true;
    }

    public function lex(Lexer $lexer): Token
    {
        // Parse header row
        $headerRow = $this->parseRow($lexer, isHeader: true);

        // Skip separator row
        $lexer->consumeUntil(Lexer::NEW_LINE);
        // TODO: determine alignment based on the separator
        $lexer->consumeWhile(Lexer::NEW_LINE);

        $rows = [$headerRow];

        // Parse data rows
        while ($lexer->comesNext('|', 1)) {
            $rows[] = $this->parseRow($lexer, isHeader: false);
        }

        return new TableToken($rows);
    }

    private function parseRow(Lexer $lexer, bool $isHeader): TableRow
    {
        $line = $lexer->consumeUntil(Lexer::NEW_LINE);
        $lexer->consumeWhile(Lexer::NEW_LINE);

        if (str_starts_with($line, '|')) {
            $line = substr($line, 1);
        }

        if (str_ends_with($line, '|')) {
            $line = substr($line, 0, -1);
        }

        $cells = $line |> (fn ($x) => explode('|', $x)) |> (fn ($x) => array_map(trim(...), $x)) |> array_values(...);

        return new TableRow($cells, $isHeader);
    }
}
