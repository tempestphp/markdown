<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;

final readonly class Markdown
{
    private Parser $parser;

    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
    ) {
        $this->parser = new Parser(highlighter: $this->highlighter);
    }

    public function parse(string $content): ParsedMarkdown
    {
        return $this->parser->parse($content);
    }
}
