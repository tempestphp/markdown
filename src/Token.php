<?php

namespace Tempest\Markdown;

interface Token
{
    public function parse(Parser $parser): string;
}
