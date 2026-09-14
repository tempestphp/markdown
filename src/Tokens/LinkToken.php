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
        public ?string $title = null,

        // @todo(aidan-casey): This is a temporary solution to the problem that we don't support Markdown escaping yet.
        public bool $parseContent = true,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $this->content;

        if ($this->parseContent) {
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
        }

        $href = $this->href ?? '';
        $blank = '';

        if (str_starts_with($href, '*')) {
            $href = substr($href, 1);
            $blank = ' target="_blank" rel="noopener noreferrer"';
        }

        $title = $this->title === null
            ? ''
            : ' title="' . htmlspecialchars($this->title, ENT_QUOTES) . '"';

        return (
            '<a href="'
            . htmlspecialchars($href, ENT_QUOTES)
            . '"'
            . $title
            . "{$blank}>{$content}</a>"
        );
    }
}
