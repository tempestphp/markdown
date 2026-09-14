<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class ImageToken implements Token
{
    public function __construct(
        public string $src,
        public ?string $alt,
        public ?string $title = null,
    ) {}

    public function parse(Parser $parser): string
    {
        if ($parser->imageFactory) {
            return $parser->imageFactory->create($this->src, $this->alt)->html;
        }

        $alt = $this->alt
            ? ' alt="' . htmlspecialchars($this->alt, ENT_QUOTES) . '"'
            : '';

        $title = $this->title === null
            ? ''
            : ' title="' . htmlspecialchars($this->title, ENT_QUOTES) . '"';

        return (
            '<img src="'
            . htmlspecialchars($this->src, ENT_QUOTES)
            . '"'
            . $alt
            . $title
            . '>'
        );
    }
}
