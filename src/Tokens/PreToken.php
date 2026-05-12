<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class PreToken implements Token
{
    public function __construct(
        public ?string $language,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        return "<pre><code class=\"language-{$this->language}\">{$this->content}</code></pre>";
    }
}
