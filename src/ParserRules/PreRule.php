<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;

final readonly class PreRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('```', 3);
    }

    public function parse(Parser $parser): string
    {
        $parser->consumeIncluding('```');

        $language = $parser->consumeUntil(Parser::NEW_LINE);

        $parser->consumeWhile(Parser::NEW_LINE);

        $content = $parser->consumeUntilString('```');

        $parser->consumeIncluding('```');
        $parser->consumeWhile(Parser::NEW_LINE);

        // Remove trailing newline.
        if (str_ends_with($content, PHP_EOL)) {
            $content = substr($content, 0, -1);
        }

        $language = $language ?: null;

        if (! $language && $parser->highlighter) {
            $language = $parser->highlighter->fallbackLanguage?->getName();
        }

        if ($parser->highlighter) {
            $content = $parser->highlighter->parse($content, $language);
        }

        $class = $language ? " class=\"language-{$language}\"" : '';

        return "<pre><code{$class}>{$content}</code></pre>";
    }
}
