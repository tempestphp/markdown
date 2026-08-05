<?php

namespace Tempest\Markdown\Exceptions;

use Exception;
use Tempest\Markdown\MarkdownException;

final class MaximumNestingDepthWasExceeded extends Exception implements MarkdownException
{
    public function __construct(int $maxNestingDepth)
    {
        parent::__construct("Maximum nesting depth of {$maxNestingDepth} was exceeded");
    }
}
