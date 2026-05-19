<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class HeadingRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('#', 1);
    }

    public function parse(Parser $parser): string
    {
        $buffer = $parser->consumeUntil(Parser::NEW_LINE);

        $level = strspn($buffer, '#');
        $content = substr($buffer, $level) |> trim(...);

        $tag = "h{$level}";
        $slug = $content |> trim(...) |> strtolower(...) |> (fn (string $x) => str_replace(' ', '-', $x));

        return "<{$tag} id=\"{$slug}\">{$content}</{$tag}>";
    }
}
