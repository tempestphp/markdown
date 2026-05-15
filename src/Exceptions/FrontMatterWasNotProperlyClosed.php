<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\RendersSnippet;

final class FrontMatterWasNotProperlyClosed extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Lexer $lexer)
    {
        $message = sprintf(
            "Frontmatter was not properly closed. It should always end with `---`: \n\n%s\n",
            $this->renderSnippet($lexer),
        );

        parent::__construct(message: $message);
    }
}
