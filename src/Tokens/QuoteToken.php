<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\LexerRules\TextRule;
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
                new QuoteRule(),
                new BoldRule(),
                new ItalicRule(),
                new LinkRule(),
                new ImageRule(),
                new TextRule('>*_[!'),
            )
            ->parse($this->content);

        return "<blockquote>{$content}</blockquote>";
    }
}
