<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class FrontMatterToken implements Token
{
    public function __construct(
        public array $data,
    ) {}

    public function parse(Parser $parser): string
    {
        return '';
    }
}
