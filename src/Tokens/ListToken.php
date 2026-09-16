<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class ListToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\ListItem[] */
        public array $items = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this);

        $list = '<ul>';

        foreach ($this->items as $item) {
            $content = $parser->parse($item->content);
            $children = $item->children?->parse($parser) ?? '';
            $list .= "<li>{$content}{$children}</li>";
        }

        $list .= '</ul>';

        return $list;
    }
}
