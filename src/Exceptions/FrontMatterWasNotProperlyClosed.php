<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\Parser;
use Tempest\Markdown\RendersSnippet;

final class FrontMatterWasNotProperlyClosed extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Parser $parser)
    {
        $message = sprintf(
            "Frontmatter was not properly closed. It should always end with `---`: \n\n%s\n",
            $this->renderSnippet($parser),
        );

        parent::__construct(message: $message);
    }
}
