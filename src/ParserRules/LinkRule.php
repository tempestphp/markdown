<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;

final class LinkRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '[';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('[', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('[');
        $content = $parser->consumeUntil(']');
        $parser->consumeIncluding(']');

        $href = null;

        if ($parser->comesNext('(', 1)) {
            $parser->consumeIncluding('(');
            $href = $parser->consumeUntil(')');
            $parser->consumeIncluding(')');
        }

        return new LinkToken($content, $href);
    }
}
