<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final class StrikethroughToken implements Token
{
    private static Parser $parser;

    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        if (! isset(self::$parser)) {
            self::$parser = $parser->withRules(
                new BoldAndItalicRule(),
                new BoldRule(),
                new ItalicRule(),
                new LinkRule(),
                new TextRule(),
            );
        }

        $content = self::$parser->parse($this->content);

        return "<s>{$content}</s>";
    }
}
