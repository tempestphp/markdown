<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class HeadingToken implements Token
{
    public function __construct(
        public string $content,
        public int $level,
    ) {}

    public function parse(Parser $parser): string
    {
        $tag = "h{$this->level}";

        $slug = $this->content |> trim(...) |> strtolower(...) |> (fn (string $x) => str_replace(' ', '-', $x));

        $id = " id=\"{$slug}\"";

        return "<{$tag}{$id}>{$this->content}</{$tag}>";
    }
}
