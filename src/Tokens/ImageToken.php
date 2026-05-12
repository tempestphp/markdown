<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\Parser;

final readonly class ImageToken implements Token
{
    public function __construct(
        public string $href,
        public string|null $alt,
    ) {}

    public function parse(Parser $parser): string
    {
        $alt = $this->alt ? " alt=\"{$this->alt}\"" : '';

        return "<img src=\"{$this->href}\"{$alt}>";
    }
}