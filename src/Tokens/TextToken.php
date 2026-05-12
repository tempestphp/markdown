<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class TextToken implements Token
{
    public function __construct(
        private(set) string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        return $this->content;
    }

    public function append(string $content): void
    {
        $this->content .= $content;
    }
}