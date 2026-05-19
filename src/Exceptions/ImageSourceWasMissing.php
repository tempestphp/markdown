<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;
use Tempest\Markdown\Parser;
use Tempest\Markdown\RendersSnippet;

final class ImageSourceWasMissing extends Exception implements MarkdownException
{
    use RendersSnippet;

    public function __construct(Parser $parser)
    {
        parent::__construct(sprintf(
            "Image source was missing:\n\n%s\n",
            trim($this->renderSnippet($parser)),
        ));
    }
}
