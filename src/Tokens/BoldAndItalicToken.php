<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class BoldAndItalicToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this, [
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        return "<strong><em>{$content}</em></strong>";
    }
}
