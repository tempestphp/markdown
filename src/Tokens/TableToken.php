<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class TableToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\TableRow[] */
        public array $rows = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
            new BoldAndItalicRule(),
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new CodeRule(),
            new ImageRule(),
            new TextRule(),
        ]);

        $headerRows = array_values(array_filter($this->rows, fn (TableRow $row) => $row->isHeader));
        $dataRows = array_values(array_filter($this->rows, fn (TableRow $row) => ! $row->isHeader));

        $table = '<table>';

        if ($headerRows !== []) {
            $table .= '<thead>';

            foreach ($headerRows as $row) {
                $table .= '<tr>';

                foreach ($row->cells as $cell) {
                    $table .= '<th>' . $parser->parse($cell)->html . '</th>';
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
                    $table .= '<td>' . $parser->parse($cell)->html . '</td>';
                }

                $table .= '</tr>';
            }

            $table .= '</tbody>';
        }

        $table .= '</table>';

        return $table;
    }
}
