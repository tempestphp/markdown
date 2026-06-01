<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\CodeRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\StrikethroughRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class HtmlToken implements Token
{
    public function __construct(
        public string $html,
    ) {}

    public function parse(Parser $parser): string
    {
        return $parser
            ->withRules(
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new ImageRule(),
                new CodeRule(),
                new TextRule(),
            )
            ->parse($this->html)
            ->html;
    }
}
