<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TableRow;
use Tempest\Markdown\Tokens\TableToken;

final readonly class TableRule implements Rule, ProvidesFirstChar
{
    public function __construct(
        public string $firstChar = '|',
    ) {}

    public function shouldParse(Parser $parser): bool
    {
        if (! $parser->comesNext('|', 1)) {
            return false;
        }

        if ($parser->lastToken instanceof TableToken) {
            return true;
        }

        $nextTwoLines = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

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

        // Filter out separator rows
        $isSeparator = trim($line, ':| -') === '' && str_contains($line, '-');

        if ($isSeparator) {
            // TODO: determine alignment based on the separator
            return null;
        }

        // Create cells
        if (str_starts_with($line, '|')) {
            $line = substr($line, 1);
        }

        if (str_ends_with($line, '|')) {
            $line = substr($line, 0, -1);
        }

        $cells = $line |> (fn ($x) => explode('|', $x)) |> (fn ($x) => array_map(trim(...), $x)) |> array_values(...);

        // Determine if is header row
        $token = $parser->lastToken;
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
