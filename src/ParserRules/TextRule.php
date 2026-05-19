<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\NeedsRules;
use Tempest\Markdown\NeedsStopChars;
use Tempest\Markdown\Parser;
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

    public function shouldParse(Parser $parser): bool
    {
        return true;
    }

    public function parse(Parser $parser): Token
    {
        $text = '';

        while ($parser->current !== null) {
            if ($this->stopChars !== '') {
                $chunk = $parser->consumeUntil($this->stopChars);
                $text .= $chunk;

                if ($chunk === '') {
                    // At a stop char — check if another rule would fire here
                    foreach ($this->otherRules as $rule) {
                        if ($rule->shouldParse($parser)) {
                            return new TextToken($text);
                        }
                    }

                    // No other rule fires — consume the stop char as plain text
                    $text .= $parser->consume();
                }
            } else {
                $text .= $parser->consume();
            }
        }

        return new TextToken($text);
    }
}
