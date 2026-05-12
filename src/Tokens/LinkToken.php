<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class LinkToken implements Token
{
    public function __construct(
        public string $content,
        public ?string $href,
    ) {}

    public function parse(Parser $parser): string
    {
        return "<a href=\"{$this->href}\">{$this->content}</a>";
    }
}
