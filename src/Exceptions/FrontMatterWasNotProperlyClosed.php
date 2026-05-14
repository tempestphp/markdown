<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;

final class FrontMatterWasNotProperlyClosed extends Exception implements MarkdownException
{
    public function __construct(
        private readonly string $content,
    ) {
        $message = sprintf(
            "Frontmatter was not properly closed. It should always end with `---`: \n\n```\n%s\n```\n",
            rtrim($this->content, "\n"),
        );

        parent::__construct(message: $message);
    }
}
