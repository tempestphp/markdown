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
        $highlighter = $parser->highlighter;

        $language = $this->language ?? 'txt';

        $content = $highlighter->parse($this->content, $language);

        return "<pre><code class=\"language-{$language}\">{$content}</code></pre>";
    }
}
