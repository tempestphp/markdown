<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldAndItalicRule;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\CodeRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\StrikethroughRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
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
