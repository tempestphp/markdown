<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class RawToken implements Token
{
    public function __construct(
        private string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        return $this->content;
    }
}
