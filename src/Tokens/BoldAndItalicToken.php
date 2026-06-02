<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\StrikethroughRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class BoldAndItalicToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<strong><em>{$content}</em></strong>";
    }
}
