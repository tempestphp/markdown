<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Tokens\TableRow;

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

    public function parse(Parser $parser): string
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

        $inlineParser = $parser->withRules(
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new CodeRule(),
            new ImageRule(),
            new TextRule(),
        );

        $headerRows = array_values(array_filter($rows, fn (TableRow $row) => $row->isHeader));
        $dataRows = array_values(array_filter($rows, fn (TableRow $row) => ! $row->isHeader));

        $table = '<table>';

        if ($headerRows !== []) {
            $table .= '<thead>';

            foreach ($headerRows as $row) {
                $table .= '<tr>';

                foreach ($row->cells as $cell) {
                    $table .= '<th>' . $inlineParser->parse($cell)->html . '</th>';
                }

                $table .= '</tr>';
            }

            $table .= '</thead>';
        }

        if ($dataRows !== []) {
            $table .= '<tbody>';

            foreach ($dataRows as $row) {
                $table .= '<tr>';

                foreach ($row->cells as $cell) {
                    $table .= '<td>' . $inlineParser->parse($cell)->html . '</td>';
                }

                $table .= '</tr>';
            }

            $table .= '</tbody>';
        }

        $table .= '</table>';

        return $table;
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
