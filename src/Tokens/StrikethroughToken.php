<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class StrikethroughToken implements Token
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
                new LinkRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        return "<s>{$content}</s>";
    }
}
