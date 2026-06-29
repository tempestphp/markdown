<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\BoldToken;

final class BoldRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private(set) string $firstChar = '*_';
    private(set) string $stopChar = '*_';

    public function shouldParse(Parser $parser): bool
    {
        $stopToken = $parser->lookaheadUntil('*_')[0] ?? null;

        if (! $stopToken) {
            return false;
        }

        $lookahead = $parser->lookaheadUntil($stopToken, $stopToken, $stopToken, $stopToken);

        if (count($lookahead) !== 4) {
            return false;
        }

        if ($lookahead[1] !== $stopToken) {
            return false;
        }

        if ($lookahead[3] !== $stopToken) {
            return false;
        }

        $content = $lookahead[2];

        $lastChar = substr($content, strlen($content) - 1, 1);

        if ($lastChar !== $stopToken) {
            return false;
        }

        return true;
    }

    public function parse(Parser $parser): Token
    {
        $startToken = $parser->consume(length: 2);
        $buffer = $parser->consumeUntil($startToken[0]);
        $parser->consume(length: 2);

        return new BoldToken($buffer);
    }
}
