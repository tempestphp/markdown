<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\EscapeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\SocialHandleRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class ItalicToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this, [
                new BoldRule(),
                new StrikethroughRule(),
                new CodeRule(),
                new LinkRule(),
                new SocialHandleRule(),
                new ImageRule(),
                new EscapeRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        return "<em>{$content}</em>";
    }
}
