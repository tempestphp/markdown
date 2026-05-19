<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\NeedsRules;
use Tempest\Markdown\NeedsStopChars;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\TextToken;

final class TextRule implements Rule, NeedsStopChars, NeedsRules
{
    public function __construct(
        public string $stopChars = '',
        /** @var Rule[] */
        public array $otherRules = [],
    ) {}

    public function shouldLex(Lexer $lexer): bool
    {
        return true;
    }

    public function lex(Lexer $lexer): Token
    {
        $text = '';

        while ($lexer->current !== null) {
            if ($this->stopChars !== '') {
                $chunk = $lexer->consumeUntil($this->stopChars);
                $text .= $chunk;

                if ($chunk === '') {
                    // At a stop char — check if another rule would fire here
                    foreach ($this->otherRules as $rule) {
                        if ($rule->shouldLex($lexer)) {
                            return new TextToken($text);
                        }
                    }

                    // No other rule fires — consume the stop char as plain text
                    $text .= $lexer->consume();
                }
            } else {
                $text .= $lexer->consume();
            }
        }

        return new TextToken($text);
    }
}
