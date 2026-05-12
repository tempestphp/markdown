<?php

namespace Tempest\Markdown;

final readonly class MarkdownParser
{
    private MarkdownLexer $lexer;

    public function __construct()
    {
        $this->lexer = new MarkdownLexer();
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