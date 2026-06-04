<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\QuoteRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class QuoteToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this, [
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new QuoteRule(),
                new LinkRule(),
                new ImageRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        return "<blockquote>{$content}</blockquote>";
    }
}
