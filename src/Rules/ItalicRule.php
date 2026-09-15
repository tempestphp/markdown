<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\RuleContext;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldAndItalicToken;
use Tempest\Markdown\Tokens\ItalicToken;

final class ItalicRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    use IsRule;

    private(set) string $firstChar = '*_';
    private(set) string $stopChar = '*_';

    public function __construct()
    {
        $this->contexts = [RuleContext::INLINE];

        $this->removeTokenSupport(ItalicToken::class);
        $this->removeTokenSupport(BoldAndItalicToken::class);
    }

    public function shouldParse(Parser $parser): bool
    {
        $stopToken = $parser->lookaheadUntil('*_')[0] ?? null;

        if (! $stopToken) {
            return false;
        }

        $lookahead = $parser->lookaheadUntil($stopToken, $stopToken);

        if (count($lookahead) !== 2) {
            return false;
        }

        $end = $lookahead[1];

        $firstChar = substr($end, 0, 1);
        $lastChar = substr($end, strlen($end) - 1, 1);

        if ($firstChar === $stopToken) {
            return false;
        }

        if ($lastChar !== $stopToken) {
            return false;
        }

        return true;
    }

    public function parse(Parser $parser): Token
    {
        $startToken = $parser->consume();
        $buffer = $parser->consumeUntil($startToken);
        $parser->consume();

        return new ItalicToken($buffer);
    }
}
