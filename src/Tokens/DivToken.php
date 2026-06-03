<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\CodeRule;
use Tempest\Markdown\ParserRules\HeadingRule;
use Tempest\Markdown\ParserRules\ImageRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\PreRule;
use Tempest\Markdown\ParserRules\QuoteRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final class DivToken implements Token
{
    private static Parser $parser;

    public function __construct(
        public ?string $class,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        if (! isset(self::$parser)) {
            self::$parser = $parser->withRules(
                new HeadingRule(),
                new QuoteRule(),
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new LinkRule(),
                new ImageRule(),
                new PreRule(),
                new CodeRule(),
                new TextRule(),
            );
        }

        $content = self::$parser->parse($this->content);

        $class = $this->class ? " class=\"{$this->class}\"" : '';

        return "<div{$class}>{$content}</div>";
    }
}
