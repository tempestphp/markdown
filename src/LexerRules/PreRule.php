<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\PreToken;

final readonly class PreRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('```', 3);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('```');

        $language = $parser->consumeUntil(Parser::WHITESPACE);

        $parser->consumeUntil(Parser::NEW_LINE);

        $parser->consumeWhile(Parser::NEW_LINE);

        $content = $parser->consumeUntilString('```');

        $parser->consumeIncluding('```');
        $parser->consumeWhile(Parser::NEW_LINE);

        // Remove trailing newline.
        if (str_ends_with($content, PHP_EOL)) {
            $content = substr($content, 0, -1);
        }

        return new PreToken(
            language: $language ?: null,
            content: $content,
        );
    }
}
