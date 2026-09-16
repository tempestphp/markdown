<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\RuleContext;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\StrikethroughToken;
use Tempest\Markdown\Tokens\TextToken;

final class StrikethroughRule implements
    Rule,
    ProvidesFirstChar,
    ProvidesStopChar
{
    use IsRule;

    private(set) string $firstChar = '~';
    private(set) string $stopChar = '~';

    public function __construct()
    {
        $this->contexts = [RuleContext::INLINE];

        $this->removeTokenSupport(StrikethroughToken::class);
    }

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('~', 1);
    }

    public function parse(Parser $parser): Token
    {
        $opening = $parser->consumeWhile('~');

        if (strpos($parser->content, '~', $parser->position) === false) {
            return new TextToken($opening);
        }

        $buffer = $parser->consumeUntil('~');
        $parser->consumeWhile('~');

        return new StrikethroughToken($buffer);
    }
}
