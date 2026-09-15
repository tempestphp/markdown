<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class ParagraphToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this);

        $content = $parser->parse($this->content);

        return "<p>{$content}</p>";
    }
}
