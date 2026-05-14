<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;

final class ImageSourceWasMissing extends Exception implements MarkdownException
{
    public function __construct()
    {
        parent::__construct('Image source was missing.');
    }
}
