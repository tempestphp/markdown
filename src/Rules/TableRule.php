<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TableRow;
use Tempest\Markdown\Tokens\TableToken;

final class TableRule implements Rule, ProvidesFirstChar
{
    public string $firstChar = '|';

    public function shouldParse(Parser $parser): bool
    {
        if (! $parser->comesNext('|', 1)) {
            return false;
        }

        if ($parser->lastToken instanceof TableToken) {
            return true;
        }

        $nextTwoLines = $parser->lookaheadUntil(
            Parser::NEW_LINE,
            Parser::NEW_LINE,
        );

        if (count($nextTwoLines) !== 2) {
            return false;
        }

        // The second line must be a separator and only contain these characters: `:| -\r\n`
        if (trim($nextTwoLines[1], ":| -\r\n") !== '') {
            return false;
        }

        // The separate line MUST contain at least one dash
        if (! str_contains($nextTwoLines[1], '-')) {
            return false;
        }

        return true;
    }

    public function parse(Parser $parser): ?Token
    {
        $line = $parser->consumeUntil(Parser::NEW_LINE);
        $parser->consumeWhile(Parser::NEW_LINE);

        $token = $parser->lastToken;

        // Only the row right after the header is a separator, later rows that look like one are data rows
        $isSeparator =
            $token instanceof TableToken
            && $token->alignments === []
            && trim($line, ':| -') === ''
            && str_contains($line, '-');

        if ($isSeparator) {
            $token->alignments = array_map(
                $this->alignment(...),
                $this->cells($line),
            );

            return null;
        }

        $cells = $this->cells($line);

        // Determine if is header row
        $isHeader = ! $token instanceof TableToken;

        $row = new TableRow($cells, $isHeader);

        if ($isHeader) {
            return new TableToken([$row]);
        }

        /** @var TableToken $token */
        $token->rows[] = $row;

        return null;
    }

    /**
     * @return list<string>
     */
    private function cells(string $line): array
    {
        if (str_starts_with($line, '|')) {
            $line = substr($line, 1);
        }

        if (str_ends_with($line, '|')) {
            $line = substr($line, 0, -1);
        }

        return $line
            |> (fn ($x) => explode('|', $x))
            |> (fn ($x) => array_map(trim(...), $x))
            |> array_values(...);
    }

    /**
     * `:---` aligns left, `:---:` center and `---:` right
     */
    private function alignment(string $separator): ?string
    {
        return match ([
            str_starts_with($separator, ':'),
            str_ends_with($separator, ':'),
        ]) {
            [true, true] => 'center',
            [true, false] => 'left',
            [false, true] => 'right',
            default => null,
        };
    }
}
