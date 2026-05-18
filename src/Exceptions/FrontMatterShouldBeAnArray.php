<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\RendersSnippet;

final class FrontMatterShouldBeAnArray extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Lexer $lexer)
    {
        $snippet = $this->renderSnippet($lexer);

        parent::__construct("Front matter can only be an array:\n\n{$snippet}\n");
    }
}
