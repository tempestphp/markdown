<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Symfony\Component\Yaml\Exception\ParseException;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\Parser;
use Tempest\Markdown\RendersSnippet;

final class FrontMatterCouldNotBeParsed extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Parser $parser, ParseException $cause)
    {
        $message = sprintf(
            "Could not parse FrontMatter: %s \n\n%s\n",
            $cause->getMessage(),
            $this->renderSnippet($parser),
        );

        parent::__construct(message: $message, previous: $cause);
    }
}
