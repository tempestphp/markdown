<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\MarkdownParser;

final readonly class QuoteToken implements Token
{
    public function __construct(
        public string $content,
        public int $level,
    ) {}

    public function parse(MarkdownParser $parser): string
    {
        // TODO: level

        return "<blockquote>{$this->content}</blockquote>";
    }
}