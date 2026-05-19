<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\ParagraphToken;

final readonly class ParagraphRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return true;
    }

    public function parse(Parser $parser): Token
    {
        $content = $parser->consumeUntil(Parser::NEW_LINE) . $parser->consumeWhile(Parser::NEW_LINE);

        return new ParagraphToken($content);
    }
}
