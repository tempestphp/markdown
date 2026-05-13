<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;

final readonly class Markdown
{
    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
    ) {}

    public function parse(string $content): ParsedMarkdown
    {
        $parser = new Parser(
            new Lexer(),
            $this->highlighter,
        );

        return $parser->parse($content);
    }
}
