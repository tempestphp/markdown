<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class RulerToken implements Token
{
    public function __construct(
        public string $content,
        public RulerType $type,
    ) {}

    public function parse(Parser $parser): string
    {
        return '<hr/>';
    }
}
