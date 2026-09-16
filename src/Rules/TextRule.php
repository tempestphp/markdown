<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\NeedsStopChars;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\RuleContext;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TextToken;

final class TextRule implements Rule, NeedsStopChars
{
    use IsRule;

    public function __construct(
        public string $stopChars = '',
    ) {
        $this->contexts = [RuleContext::INLINE];
    }

    public function shouldParse(Parser $parser): bool
    {
        return true;
    }

    public function parse(Parser $parser): ?Token
    {
        $text = $this->stopChars !== ''
            ? $parser->consumeUntil($this->stopChars)
            : '';

        if ($text === '') {
            $text = $parser->consume();
        }

        if ($parser->lastToken instanceof TextToken) {
            $parser->lastToken->append($text);
            return null;
        }

        return new TextToken($text);
    }
}
