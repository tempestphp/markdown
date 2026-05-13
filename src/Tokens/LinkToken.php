<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class LinkToken implements Token
{
    public function __construct(
        public string $content,
        public ?string $href,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new BoldRule(),
                new ItalicRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<a href=\"{$this->href}\">{$content}</a>";
    }
}
