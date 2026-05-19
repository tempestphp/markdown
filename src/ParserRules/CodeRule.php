<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;

final class CodeRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '`';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('`', 1);
    }

    public function parse(Parser $parser): string
    {
        $parser->consumeIncluding('`');

        $language = null;

        if ($parser->comesNext('{', 1)) {
            $parser->consume();
            $language = $parser->consumeUntil('}');
            $parser->consume();
        }

        $content = $parser->consumeUntil('`');

        $parser->consumeIncluding('`');

        if (! $language && $parser->highlighter) {
            $language = $parser->highlighter->fallbackLanguage?->getName();
        }

        if ($parser->highlighter) {
            $content = $parser->highlighter->parse($content, $language);
        }

        $class = $language ? " class=\"language-{$language}\"" : '';

        return "<code{$class}>{$content}</code>";
    }
}
