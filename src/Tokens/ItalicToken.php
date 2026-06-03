<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final class ItalicToken implements Token
{
    private static Parser $parser;

    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        if (! isset(self::$parser)) {
            self::$parser = $parser->withRules(
                new BoldRule(),
                new StrikethroughRule(),
                new LinkRule(),
                new TextRule(),
            );
        }

        $content = self::$parser->parse($this->content);

        return "<em>{$content}</em>";
    }
}
