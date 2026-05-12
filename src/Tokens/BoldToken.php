<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class BoldToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        return "<strong>{$this->content}</strong>";
    }
}
