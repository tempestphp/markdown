<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Tempest\Markdown\Tokens\FrontMatterToken;

final readonly class Parser
{
    public function __construct(
        public Lexer $lexer = new Lexer(),
        public ?Highlighter $highlighter = new Highlighter(),
    ) {}

    public function withRules(Rule ...$rules): self
    {
        return clone($this, [
            'lexer' => $this->lexer->withRules(...$rules),
        ]);
    }

    public function parse(string $input): ParsedMarkdown
    {
        $tokens = $this->lexer->lex($input);

        $html = '';

        $frontMatter = [];

        foreach ($tokens as $token) {
            $html .= $token->parse($this);

            if ($token instanceof FrontMatterToken) {
                $frontMatter = [...$frontMatter, ...$token->data];
            }
        }

        return new ParsedMarkdown($html, $frontMatter);
    }
}
