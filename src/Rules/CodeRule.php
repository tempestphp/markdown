<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\RuleContext;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\CodeToken;

final class CodeRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    use IsRule;

    private(set) string $firstChar = '`';
    private(set) string $stopChar = '`';

    public function __construct()
    {
        $this->contexts = [RuleContext::INLINE];
    }

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('`', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('`');

        $language = null;

        if ($parser->comesNext('{', 1)) {
            $parser->consume();
            $language = $parser->consumeUntil('}');
            $parser->consume();
        }

        $content = '';

        if ($language && ! ctype_alpha($language)) {
            $content .= '{' . $language . '}';
            $language = null;
        }

        $content .= $parser->consumeUntil('`');

        $parser->consumeIncluding('`');

        return new CodeToken($language, $content);
    }
}
