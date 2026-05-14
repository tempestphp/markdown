<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;

final class ImageSourceWasNotClosed extends Exception implements MarkdownException
{
    public function __construct()
    {
        parent::__construct('Image source was not closed.');
    }
}
