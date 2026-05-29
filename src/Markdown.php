<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

final readonly class Markdown
{
    private Parser $parser;

    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
        private ?ResponsiveImageFactory $imageFactory = null,
    ) {
        $this->parser = new Parser(
            new Lexer(),
            $this->highlighter,
            $this->imageFactory,
        );
    }

    public function parse(string $content): ParsedMarkdown
    {
        return $this->parser->parse($content);
    }
}
