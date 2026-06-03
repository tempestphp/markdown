<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\CodeRule;
use Tempest\Markdown\ParserRules\ImageRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
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
                new BoldAndItalicRule(),
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
