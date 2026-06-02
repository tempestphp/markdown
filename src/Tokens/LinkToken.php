<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldAndItalicRule;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\StrikethroughRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
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
