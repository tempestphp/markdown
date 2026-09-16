<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class LinkToken implements Token
{
    public function __construct(
        public string $content,
        public ?string $href,

        // @todo(aidan-casey): This is a temporary solution to the problem that we don't support Markdown escaping yet.
        public bool $parseContent = true,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $this->content;

        if ($this->parseContent) {
            $content = $parser
                ->forToken($this)
                ->parse($this->content);
        }

        $href = $this->href ?? '';
        $blank = '';

        if (str_starts_with($href, '*')) {
            $href = substr($href, 1);
            $blank = ' target="_blank" rel="noopener noreferrer"';
        }

        return (
            '<a href="'
            . htmlspecialchars($href, ENT_QUOTES)
            . "\"{$blank}>{$content}</a>"
        );
    }
}
