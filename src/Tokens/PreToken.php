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
        $language = $this->language;

        if (! $language && $parser->highlighter) {
            $language = $parser->highlighter->fallbackLanguage?->getName();
        }

        if ($parser->highlighter) {
            $content = $parser->highlighter->parse($this->content, $language);
        } else {
            $content = $this->content;
        }

        $class = $language ? " class=\"language-{$language}\"" : '';

        return "<pre{$class}>{$content}</pre>";
    }
}
