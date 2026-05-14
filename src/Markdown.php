<?php

declare(strict_types=1);

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;

final readonly class Markdown
{
    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
    ) {}

    public function parse(string $content): ParsedMarkdown
    {
        return parse_markdown($content, $this->highlighter);
    }
}
