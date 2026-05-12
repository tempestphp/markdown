<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;
use Tempest\Markdown\MarkdownParser;

final class ParagraphToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(MarkdownParser $parser): string
    {
        $content = $parser->parse($this->content);

        return "<p>{$content}</p>";
    }
}