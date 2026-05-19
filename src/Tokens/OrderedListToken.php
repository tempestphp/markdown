<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\CodeRule;
use Tempest\Markdown\ParserRules\ImageRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final class OrderedListToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\ListItem[] */
        public array $items = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $inlineParser = $parser->withRules(
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule(),
        );

        $list = '<ol>';

        foreach ($this->items as $item) {
            $content = $inlineParser->parse($item->content);
            $children = $item->children ?? '';
            $list .= "<li>{$content}{$children}</li>";
        }

        $list .= '</ol>';

        return $list;
    }
}
