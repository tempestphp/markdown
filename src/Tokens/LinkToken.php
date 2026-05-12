<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\Parser;

final readonly class LinkToken implements Token
{
    public function __construct(
        public string $content,
        public string|null $href,
    ) {}

    public function parse(Parser $parser): string
    {
        return "<a href=\"{$this->href}\">{$this->content}</a>";
    }
}