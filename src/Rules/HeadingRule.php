<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\DivToken;
use Tempest\Markdown\Tokens\HeadingToken;

final class HeadingRule implements Rule, ProvidesFirstChar
{
    use IsRule;

    public string $firstChar = '#';

    public function __construct(
        /**
         * Whether a heading without an explicit id gets one slugged from its
         * content. An id written as `## Title ## id` is kept either way.
         */
        public bool $generateIds = true,
    ) {
        $this->addTokenSupport(DivToken::class);
    }

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('#', 1);
    }

    public function parse(Parser $parser): Token
    {
        $buffer = $parser->consumeUntil(Parser::NEW_LINE) |> trim(...);

        $level = strspn($buffer, '#');

        $buffer = substr(string: $buffer, offset: $level) |> trim(...);

        $idSeparator = strpos(
            haystack: $buffer,
            needle: str_repeat('#', $level),
            offset: 0,
        );

        if ($idSeparator !== false) {
            // An id is specified
            $id = substr(string: $buffer, offset: $idSeparator + $level)
                |> trim(...);
            $buffer = substr(string: $buffer, offset: 0, length: $idSeparator)
                |> trim(...);
        } elseif ($this->generateIds) {
            // No id is specified, we'll slug the heading
            $id = $buffer
                |> mb_strtolower(...)
                |> (fn (string $x) => trim(
                    preg_replace('/[^\p{L}\p{N}]+/u', '-', $x) ?? '',
                    '-',
                ));
        } else {
            $id = null;
        }

        return new HeadingToken(
            content: $buffer,
            level: $level,
            id: $id,
        );
    }
}
