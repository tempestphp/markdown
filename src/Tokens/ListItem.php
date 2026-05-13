<?php

namespace Tempest\Markdown\Tokens;

use Tempest\Markdown\Token;

final readonly class ListItem {
    public function __construct(
        public string $content,
        public ?Token $children = null,
    ) {}
}
