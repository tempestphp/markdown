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

final class OrderedListToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\ListItem[] */
        public array $items = [],

        /** The number the list starts at, as written in the first item's marker. */
        public int $start = 1,
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
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
        ]);

        $list = $this->start === 1
            ? '<ol>'
            : '<ol start="' . $this->start . '">';

        foreach ($this->items as $item) {
            $content = $parser->parse($item->content);
            $children = $item->children?->parse($parser) ?? '';
            $list .= "<li>{$content}{$children}</li>";
        }

        $list .= '</ol>';

        return $list;
    }
}
