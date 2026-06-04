<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class HeadingToken implements Token
{
    public function __construct(
        public string $content,
        public int $level,
    ) {}

    public function parse(Parser $parser): string
    {
        $tag = "h{$this->level}";

        $slug = $this->content |> trim(...) |> strtolower(...) |> (fn (string $x) => str_replace(' ', '-', $x));

        $id = " id=\"{$slug}\"";

        $content = $parser
            ->forToken($this, [
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new CodeRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        return "<{$tag}{$id}>{$content}</{$tag}>";
    }
}
