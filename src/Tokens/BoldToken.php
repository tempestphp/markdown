<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\MarkdownParser;

final readonly class BoldToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(MarkdownParser $parser): string
    {
        return "<strong>{$this->content}</strong>";
    }
}