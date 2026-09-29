<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\EscapeRule;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\PreRule;
use Tempest\Markdown\Rules\QuoteRule;
use Tempest\Markdown\Rules\SocialHandleRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class DivToken implements Token
{
    public function __construct(
        public ?string $class,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this, [
                new HeadingRule(),
                new QuoteRule(),
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new SocialHandleRule(),
                new ImageRule(),
                new PreRule(),
                new CodeRule(),
                new EscapeRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        $class = $this->class
            ? ' class="' . htmlspecialchars($this->class, ENT_QUOTES) . '"'
            : '';

        return "<div{$class}>{$content}</div>";
    }
}
