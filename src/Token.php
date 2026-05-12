<?php

namespace Tempest\Markdown;

interface Token
{
    public function parse(MarkdownParser $parser): string;
}