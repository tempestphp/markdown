<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\IsRule;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\DivToken;
use Tempest\Markdown\Tokens\ParagraphToken;
use Tempest\Markdown\Tokens\PreToken;

final class PreRule implements Rule, ProvidesFirstChar
{
    use IsRule;

    public string $firstChar = '`~';

    public function __construct()
    {
        $this->addTokenSupport(DivToken::class);
        $this->addTokenSupport(ParagraphToken::class);
    }

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('```', 3) || $parser->comesNext('~~~', 3);
    }

    public function parse(Parser $parser): Token
    {
        // A fence is a run of at least three backticks or tildes, and is
        // closed by a run of the same character.
        $fence = $parser->consumeWhile($parser->current ?? '`');

        $language = $parser->consumeUntil(Parser::WHITESPACE);

        $title = trim($parser->consumeUntil(Parser::NEW_LINE));

        $parser->consumeWhile(Parser::NEW_LINE);

        $content = $parser->consumeUntilString($fence);

        $parser->consumeIncluding($fence);
        $parser->consumeWhile(Parser::NEW_LINE);

        // Remove trailing newline.
        if (str_ends_with($content, PHP_EOL)) {
            $content = substr($content, 0, -1);
        }

        return new PreToken(
            language: $language ?: null,
            content: $content,
            title: $title ?: null,
        );
    }
}
