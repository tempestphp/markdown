<?php

namespace Tempest\Markdown;

final class Parser
{
    public function __construct(
        private Lexer $lexer = new Lexer(),
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
