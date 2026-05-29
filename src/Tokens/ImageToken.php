<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class ImageToken implements Token
{
    public function __construct(
        public string $src,
        public ?string $alt,
    ) {}

    public function parse(Parser $parser): string
    {
        if ($parser->imageFactory) {
            return $parser->imageFactory->create($this->src, $this->alt)->html;
        }

        $alt = $this->alt ? " alt=\"{$this->alt}\"" : '';

        return "<img src=\"{$this->src}\"{$alt}>";
    }
}
