<?php

namespace Tempest\Markdown;

interface NeedsRules
{
    /** @var Rule[] */
    public array $otherRules { get; set; }
}
