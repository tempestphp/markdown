<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldAndItalicRule;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\CodeRule;
use Tempest\Markdown\ParserRules\DivRule;
use Tempest\Markdown\ParserRules\ImageRule;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\ParserRules\LinkRule;
use Tempest\Markdown\ParserRules\PreRule;
use Tempest\Markdown\ParserRules\StrikethroughRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Token;

final class ParagraphToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->forToken($this, [
            new BoldAndItalicRule(),
            new BoldRule(),
            new ItalicRule(),
            new StrikethroughRule(),
            new LinkRule(),
            new ImageRule(),
            new PreRule(),
            new CodeRule(),
            new DivRule(),
            new TextRule(),
        ]);

        $content = $parser->parse($this->content);

        return "<p>{$content}</p>";
    }
}
