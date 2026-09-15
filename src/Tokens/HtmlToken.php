<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class HtmlToken implements Token
{
    public function __construct(
        public string $html,

        /** Whether the content is raw text that must never be parsed as Markdown. */
        public bool $raw = false,
    ) {}

    public function parse(Parser $parser): string
    {
        if ($this->raw) {
            return $this->html;
        }

        return $parser
            ->forToken($this)
            ->parse($this->html)
            ->html;
    }
}
