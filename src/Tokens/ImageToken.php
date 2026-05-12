<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class ImageToken implements Token
{
    public function __construct(
        public string $href,
        public ?string $alt,
    ) {}

    public function parse(Parser $parser): string
    {
        $alt = $this->alt ? " alt=\"{$this->alt}\"" : '';

        return "<img src=\"{$this->href}\"{$alt}>";
    }
}
