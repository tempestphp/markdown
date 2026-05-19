<?php

namespace Tempest\Markdown\Tokens;

final readonly class ListItem
{
    public function __construct(
        public string $content,
        public ?string $children = null,
    ) {}
}
