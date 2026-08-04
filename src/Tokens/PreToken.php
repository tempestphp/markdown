<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class PreToken implements Token
{
    public function __construct(
        public ?string $language,
        public string $content,
        public ?string $title = null,
    ) {}

    public function parse(Parser $parser): string
    {
        $language = $this->language;

        if ($parser->highlighter) {
            $content = $parser->highlighter->parse($this->content, $language);
            $language = $parser->highlighter->getCurrentLanguage()?->getName();
        } else {
            $content = $this->content;
        }

        $class = $language ? ' class="language-' . htmlspecialchars($language, ENT_QUOTES) . '"' : '';

        $html = "<pre{$class}>{$content}</pre>";

        if ($this->title) {
            $html = '<div class="code-title">' . htmlspecialchars($this->title, ENT_QUOTES) . "</div>{$html}";
        }

        return $html;
    }
}
