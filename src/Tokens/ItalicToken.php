<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\MarkdownParser;

final readonly class ItalicToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(MarkdownParser $parser): string
    {
        return "<em>{$this->content}</em>";
    }
}