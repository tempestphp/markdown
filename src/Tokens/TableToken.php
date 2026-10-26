<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\SocialHandleRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class TableToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\TableRow[] */
        public array $rows = [],
        /** @var list<string|null> Alignment of each column: left, center, right or null */
        public array $alignments = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
            new BoldAndItalicRule(),
            new BoldRule(),
            new ItalicRule(),
            new StrikethroughRule(),
            new LinkRule(),
            new SocialHandleRule(),
            new CodeRule(),
            new ImageRule(),
            new TextRule(),
        ]);

        $headerRows = array_values(array_filter(
            $this->rows,
            fn (TableRow $row) => $row->isHeader,
        ));
        $dataRows = array_values(array_filter(
            $this->rows,
            fn (TableRow $row) => ! $row->isHeader,
        ));

        $table = '<table>';

        if ($headerRows !== []) {
            $table .= '<thead>';

            foreach ($headerRows as $row) {
                $table .= '<tr>';

                foreach ($row->cells as $column => $cell) {
                    $table .=
                        '<th'
                        . $this->align($column)
                        . '>'
                        . $parser->parse($cell)->html
                        . '</th>';
                }

                $table .= '</tr>';
            }

            $table .= '</thead>';
        }

        if ($dataRows !== []) {
            $table .= '<tbody>';

            foreach ($dataRows as $row) {
                $table .= '<tr>';

                foreach ($row->cells as $column => $cell) {
                    $table .=
                        '<td'
                        . $this->align($column)
                        . '>'
                        . $parser->parse($cell)->html
                        . '</td>';
                }

                $table .= '</tr>';
            }

            $table .= '</tbody>';
        }

        $table .= '</table>';

        return $table;
    }

    private function align(int $column): string
    {
        $alignment = $this->alignments[$column] ?? null;

        return $alignment === null ? '' : ' align="' . $alignment . '"';
    }
}
