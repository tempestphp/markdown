<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\Parser;
use Tempest\Markdown\RendersSnippet;

final class FrontMatterShouldBeAnArray extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Parser $parser)
    {
        $snippet = $this->renderSnippet($parser);

        parent::__construct("Front matter can only be an array:\n\n{$snippet}\n");
    }
}
