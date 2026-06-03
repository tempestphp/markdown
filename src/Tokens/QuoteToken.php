<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\ImageRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\QuoteRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class QuoteToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new QuoteRule(),
                new LinkRule(),
                new ImageRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<blockquote>{$content}</blockquote>";
    }
}
