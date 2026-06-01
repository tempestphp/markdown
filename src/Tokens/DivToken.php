<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class DivToken implements Token
{
    public function __construct(
        public ?string $class,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new HeadingRule(),
                new QuoteRule(),
                new BoldRule(),
                new ItalicRule(),
                new LinkRule(),
                new ImageRule(),
                new TextRule(),
            )
            ->parse($this->content);

        $class = $this->class ? " class=\"{$this->class}\"" : '';

        return "<div{$class}>{$content}</div>";
    }
}
