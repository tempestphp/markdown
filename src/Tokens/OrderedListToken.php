<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
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
        $parser = $parser->forToken($this);

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
