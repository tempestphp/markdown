<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\MarkdownParser;

final readonly class PreToken implements Token
{
    public function __construct(
        public ?string $language,
        public string $content,
    ) {}

    public function parse(MarkdownParser $parser): string
    {
        return "<pre><code class=\"language-{$this->language}\">{$this->content}</code></pre>";
    }
}