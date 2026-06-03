<?php

namespace Tempest\Markdown\ParserRules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;

final class LinkRule implements Rule, ProvidesFirstChar, ProvidesStopChar
{
    private(set) string $firstChar = '[';
    private(set) string $stopChar = '[';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('[', 1);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('[');
        $content = $this->consumeContent($parser);
        $parser->consumeIncluding(']');

        $href = null;

        if ($parser->comesNext('(', 1)) {
            $parser->consumeIncluding('(');
            $href = $parser->consumeUntil(')');
            $parser->consumeIncluding(')');
        }

        return new LinkToken($content, $href);
    }

    private function consumeContent(Parser $parser): string
    {
        $content = '';
        $bracketDepth = 0;

        while ($parser->current !== null) {
            if ($parser->comesNext(']') && $bracketDepth === 0) {
                break;
            }

            if ($parser->comesNext('[')) {
                $bracketDepth += 1;
            } elseif ($parser->comesNext(']')) {
                $bracketDepth -= 1;
            }

            $content .= $parser->consume();
        }

        return $content;
    }
}
