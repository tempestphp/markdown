<?php

namespace Tempest\Markdown\Tokens;

final readonly class TableRow
{
    public function __construct(
        /** @var string[] */
        public array $cells,
        public bool $isHeader,
    ) {}
}
