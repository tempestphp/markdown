<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class StrikethroughToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new ItalicRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<s>{$content}</s>";
    }
}
