<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\RendersSnippet;

final class FrontMatterCouldNotBeParsed extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Lexer $lexer, ParseException $cause)
    {
        $message = sprintf(
            "Could not parse FrontMatter: %s \n\n%s\n",
            $cause->getMessage(),
            $this->renderSnippet($lexer),
        );

        parent::__construct(message: $message, previous: $cause);
    }
}
