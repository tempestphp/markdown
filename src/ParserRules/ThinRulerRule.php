<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;

final readonly class ThinRulerRule implements Rule, ProvidesFirstChar
{
    public function __construct(
        public string $firstChar = '-',
    ) {}

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('---', 3);
    }

    public function parse(Parser $parser): Token
    {
        $content = $parser->consumeWhile('-');

        return new RulerToken(
            $content,
            RulerType::THIN,
        );
    }
}
