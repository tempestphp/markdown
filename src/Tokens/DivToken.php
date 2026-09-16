<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class DivToken implements Token
{
    public function __construct(
        public ?string $class,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this)
            ->parse($this->content);

        $class = $this->class
            ? ' class="' . htmlspecialchars($this->class, ENT_QUOTES) . '"'
            : '';

        return "<div{$class}>{$content}</div>";
    }
}
