<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\NeedsStopChars;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TextToken;

final class TextRule implements Rule, NeedsStopChars
{
    public function __construct(
        public string $stopChars = '',
    ) {}

    public function shouldLex(Lexer $lexer): bool
    {
        return true;
    }

    public function lex(Lexer $lexer): ?Token
    {
        $text = $this->stopChars !== ''
            ? $lexer->consumeUntil($this->stopChars)
            : '';

        if ($text === '') {
            $text = $lexer->consume();
        }

        if ($lexer->lastToken instanceof TextToken) {
            $lexer->lastToken->append($text);
            return null;
        }

        return new TextToken($text);
    }
}
