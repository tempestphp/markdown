<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\CodeRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final readonly class HeadingToken implements Token
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
            ->withRules(
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new CodeRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<{$tag}{$id}>{$content}</{$tag}>";
    }
}
