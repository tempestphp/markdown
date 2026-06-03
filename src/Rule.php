<?php

namespace Tempest\Markdown;

interface Rule
{
    public function shouldParse(Parser $parser): bool;

    public function parse(Parser $parser): ?Token;
}
