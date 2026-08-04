<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HeadingToken;

final class HeadingRule implements Rule, ProvidesFirstChar
{
    public string $firstChar = '#';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('#', 1);
    }

    public function parse(Parser $parser): Token
    {
        $buffer = $parser->consumeUntil(Parser::NEW_LINE) |> trim(...);

        $level = strspn($buffer, '#');

        $buffer = substr(string: $buffer, offset: $level) |> trim(...);

        $idSeparator = strpos(
            haystack: $buffer,
            needle: str_repeat('#', $level),
            offset: $level,
        );

        if ($idSeparator !== false) {
            // An id is specified
            $id = substr(string: $buffer, offset: $idSeparator + $level) |> trim(...);
            $buffer = substr(string: $buffer, offset: 0, length: $idSeparator) |> trim(...);
        } else {
            // No id is specified, we'll slug the heading
            $id = $buffer |> strtolower(...) |> (fn (string $x) => trim(preg_replace('/[^a-z0-9]+/', '-', $x), '-'));
        }

        return new HeadingToken(
            content: $buffer,
            level: $level,
            id: $id,
        );
    }
}
