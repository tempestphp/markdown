<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\CodeRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final class OrderedListToken implements Token
{
    public function __construct(
        /** @var \Tempest\Markdown\Tokens\ListItem[] */
        public array $items = [],
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->withRules(
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule('*_[!`'),
        );

        $list = '<ol>';

        foreach ($this->items as $item) {
            $content = $parser->parse($item->content);
            $children = $item->children?->parse($parser) ?? '';
            $list .= "<li>{$content}{$children}</li>";
        }

        $list .= '</ol>';

        return $list;
    }
}
