<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\RendersSnippet;

final class ImageSourceWasNotClosed extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Lexer $lexer)
    {
        parent::__construct(sprintf(
            "Image source was not closed:\n\n%s\n",
            trim($this->renderSnippet($lexer)),
        ));
    }
}
