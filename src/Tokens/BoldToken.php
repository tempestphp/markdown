<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class BoldToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<strong>{$content}</strong>";
    }
}
