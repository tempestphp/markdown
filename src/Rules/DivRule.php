<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\DivToken;
use Tempest\Markdown\Tokens\ParagraphToken;

final class DivRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    use IsRule;

    public string $stopChar = ':';
    public string $firstChar = ':';

    public function __construct()
    {
        $this->addTokenSupport(ParagraphToken::class);
    }

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
