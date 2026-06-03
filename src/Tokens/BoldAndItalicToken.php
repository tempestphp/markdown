<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final class BoldAndItalicToken implements Token
{
    private static Parser $parser;

    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        if (! isset(self::$parser)) {
            self::$parser = $parser->withRules(
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            );
        }

        $content = self::$parser->parse($this->content);

        return "<strong><em>{$content}</em></strong>";
    }
}
