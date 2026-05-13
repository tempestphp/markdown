<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Token;

final readonly class BoldToken implements Token
{
    public function __construct(
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser
            ->withRules(
                new ItalicRule(),
                new LinkRule(),
                new TextRule(),
            )
            ->parse($this->content);

        return "<strong>{$content}</strong>";
    }
}
