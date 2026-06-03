<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class HtmlCommentToken implements Token
{
    public function __construct(
        public string $html,
    ) {}

    public function parse(Parser $parser): string
    {
        return $this->html;
    }
}
