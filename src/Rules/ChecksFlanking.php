<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;

trait ChecksFlanking
{
    /**
     * An opening delimiter may not be followed by whitespace, nor a closing
     * one preceded by it.
     *
     * @see https://spec.commonmark.org/0.31.2/#left-flanking-delimiter-run
     */
    private function isFlanked(string $content): bool
    {
        return (
            $content !== ''
            && ! str_contains(Parser::WHITESPACE, $content[0])
            && ! str_contains(Parser::WHITESPACE, $content[-1])
        );
    }
}
