<?php

namespace Tempest\Markdown;

final class Markdown
{
    public function parse(string $content): string
    {
        $parser = new Parser();

        return $parser->parse($content);
    }
}
