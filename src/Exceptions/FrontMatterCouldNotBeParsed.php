<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;
use Throwable;

final class FrontMatterCouldNotBeParsed extends Exception implements MarkdownException
{
    public function __construct(
        private readonly string $content,
        private readonly Throwable $cause,
    ) {
        $message = sprintf(
            "Could not parse FrontMatter: \n\n```\n%s\n```\n\n%s\n",
            rtrim($this->content, "\n"),
            $this->cause->getMessage(),
        );

        parent::__construct(message: $message, previous: $cause);
    }
}
