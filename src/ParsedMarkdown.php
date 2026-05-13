<?php

namespace Tempest\Markdown;

use Stringable;

final readonly class ParsedMarkdown implements Stringable
{
    public function __construct(
        public string $html,
        public array $frontMatter,
    ) {}

    public function __toString(): string
    {
        return $this->html;
    }
}
