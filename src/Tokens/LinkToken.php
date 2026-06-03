<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\ImageRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final readonly class LinkToken implements Token
{
    public function __construct(
        public string $content,
        public ?string $href,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new ImageRule(),
                new TextRule(),
            )
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
