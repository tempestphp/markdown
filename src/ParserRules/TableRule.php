<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TableRow;
use Tempest\Markdown\Tokens\TableToken;

final readonly class TableRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        if (! $parser->comesNext('|', 1)) {
            return false;
        }

        $nextTwoLines = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

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

    public function parse(Parser $parser): Token
    {
        // Parse header row
        $headerRow = $this->parseRow($parser, isHeader: true);

        // Skip separator row
        $parser->consumeUntil(Parser::NEW_LINE);
        // TODO: determine alignment based on the separator
        $parser->consumeWhile(Parser::NEW_LINE);

        $rows = [$headerRow];

        // Parse data rows
        while ($parser->comesNext('|', 1)) {
            $rows[] = $this->parseRow($parser, isHeader: false);
        }

        return new TableToken($rows);
    }

    private function parseRow(Parser $parser, bool $isHeader): TableRow
    {
        $line = $parser->consumeUntil(Parser::NEW_LINE);
        $parser->consumeWhile(Parser::NEW_LINE);

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
