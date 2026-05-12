<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\CodeRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Parser;

final class ParagraphToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $parser = $parser->withRules(
            new BoldRule(),
            new ItalicRule(),
            new LinkRule(),
            new ImageRule(),
            new CodeRule(),
            new TextRule(),
        );

        $content = $parser->parse($this->content);

        return "<p>{$content}</p>";
    }
}