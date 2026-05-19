<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;

final class LinkRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '[';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('[', 1);
    }

    public function parse(Parser $parser): string
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

        $parsedContent = $parser
            ->withRules(
                new BoldRule(),
                new ItalicRule(),
                new StrikethroughRule(),
                new TextRule(),
            )
            ->parse($content);

        $href ??= '';
        $blank = '';

        if (str_starts_with($href, '*')) {
            $href = substr($href, 1);
            $blank = ' target="_blank" rel="noopener noreferrer"';
        }

        return "<a href=\"{$href}\"{$blank}>{$parsedContent}</a>";
    }
}
