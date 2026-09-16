<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class BoldAndItalicToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this)
            ->parse($this->content);

        return "<strong><em>{$content}</em></strong>";
    }
}
