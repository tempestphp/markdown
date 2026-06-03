<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\DivToken;

final readonly class DivRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext(':::', 3);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeWhile(':');

        $class = $parser->consumeUntil(Parser::NEW_LINE) ?: null;

        $parser->consumeWhile(Parser::NEW_LINE);

        $content = $parser->consumeUntilString(':::');

        $parser->consumeWhile(':');
        $parser->consumeWhile(Parser::NEW_LINE);

        return new DivToken(
            class: $class,
            content: $content,
        );
    }
}
