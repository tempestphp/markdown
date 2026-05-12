<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class QuoteToken implements Token
{
    public function __construct(
        public string $content,
        public int $level,
    ) {}

    public function parse(Parser $parser): string
    {
        // TODO: level

        return "<blockquote>{$this->content}</blockquote>";
    }
}
