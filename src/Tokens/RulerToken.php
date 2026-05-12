<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\MarkdownParser;

final readonly class RulerToken implements Token
{
    public function __construct(
        public string $content,
        public RulerType $type,
    ) {}

    public function parse(MarkdownParser $parser): string
    {
        return "<hr/>";
    }
}