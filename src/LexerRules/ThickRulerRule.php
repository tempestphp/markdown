<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\LexerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;

final readonly class ThickRulerRule implements LexerRule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('===');
    }

    public function lex(Lexer $lexer): Token
    {
        $content = $lexer->consumeWhile('=');

        return new RulerToken(
            $content,
            RulerType::THICK,
        );
    }
}
