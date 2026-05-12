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

        return "<{$tag}>{$this->content}</{$tag}>";
    }
}
