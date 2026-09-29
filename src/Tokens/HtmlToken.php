<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\EscapeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\SocialHandleRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class HtmlToken implements Token
{
    public function __construct(
        public string $html,

        /** Whether the content is raw text that must never be parsed as Markdown. */
        public bool $raw = false,
    ) {}

    public function parse(Parser $parser): string
    {
        if ($this->raw) {
            return $this->html;
        }

        return $parser
            ->forToken($this, [
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new SocialHandleRule(),
                new ImageRule(),
                new CodeRule(),
                new EscapeRule(),
                new TextRule(),
            ])
            ->parse($this->html)
            ->html;
    }
}
