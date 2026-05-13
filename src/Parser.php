<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;

final readonly class Parser
{
    public function __construct(
        public Lexer $lexer = new Lexer(),
        public Highlighter $highlighter = new Highlighter(),
    ) {}

    public function withRules(Rule ...$rules): self
    {
        return clone($this, [
            'lexer' => $this->lexer->withRules(...$rules),
        ]);
    }

    public function parse(string $input): string
    {
        $tokens = $this->lexer->lex($input);

        $html = '';

        foreach ($tokens as $token) {
            $html .= $token->parse($this);
        }

        return $html;
    }
}
