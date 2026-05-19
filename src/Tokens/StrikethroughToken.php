<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\TextRule;
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
                new BoldRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<s>{$content}</s>";
    }
}
