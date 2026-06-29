<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class LinkToken implements Token
{
    public function __construct(
        public string $content,
        public ?string $href,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->forToken($this, [
                new CodeRule(),
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new ImageRule(),
                new TextRule(),
            ])
            ->parse($this->content);

        $href = $this->href ?? '';
        $blank = '';

        if (str_starts_with($href, '*')) {
            $href = substr($href, 1);
            $blank = ' target="_blank" rel="noopener noreferrer"';
        }

        return "<a href=\"{$href}\"{$blank}>{$content}</a>";
    }
}
