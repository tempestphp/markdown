<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Token;

final class ListToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\ListItem[] */
        public array $items = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
            new BoldAndItalicRule(),
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule(),
        ]);

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
