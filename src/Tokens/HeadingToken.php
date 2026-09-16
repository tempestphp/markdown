<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class HeadingToken implements Token
{
    public function __construct(
        public string $content,
        public int $level,
        public ?string $id = null,
    ) {}

    public function parse(Parser $parser): string
    {
        $tag = "h{$this->level}";

        if ($this->id) {
            $id = ' id="' . htmlspecialchars($this->id, ENT_QUOTES) . '"';
        } else {
            $id = '';
        }

        $content = $parser
            ->forToken($this)
            ->parse($this->content);

        return "<{$tag}{$id}>{$content}</{$tag}>";
    }
}
