<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Rules\DivRule;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\PreRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
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
