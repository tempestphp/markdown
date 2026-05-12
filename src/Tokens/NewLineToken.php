<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class NewLineToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        return $this->content;
    }
}
