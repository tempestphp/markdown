<?php

namespace Tempest\Markdown\Rules;

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
            $href = $this->consumeHrefTwo($parser);
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

    private function consumeHref(Parser $parser): string
    {
        $href = '';
        $parenthesisDepth = 0;

        while ($parser->current !== null) {
            // Escaped character: consume the backslash and the following
            // character as part of the URL.
            if ($parser->comesNext('\\')) {
                $parser->consume();

                if ($parser->current !== null) {
                    $href .= $parser->consume();
                }

                continue;
            }

            // The following parentheses indicates an
            // opening parentheses in the URL, which should
            // be parsed as part of the URL.
            if ($parser->comesNext('(')) {
                $parenthesisDepth++;
                $href .= $parser->consume();
                continue;
            }

            if ($parser->comesNext(')')) {
                if ($parenthesisDepth === 0) {
                    break;
                }

                $parenthesisDepth--;
                $href .= $parser->consume();
                continue;
            }

            $href .= $parser->consume();
        }

        return $href;
    }

    private function consumeHrefTwo(Parser $parser): string
    {
        $href = '';
        $depth = 0;

        while (($current = $parser->current) !== null) {
            if ($current !== '(' && $current !== ')' && $current !== '\\') {
                $href .= $parser->consume();
                continue;
            }

            if ($current === '(') {
                $depth++;
                $href .= $parser->consume();
                continue;
            }

            if ($current === ')') {
                if ($depth === 0) {
                    break;
                }

                $depth--;
                $href .= $parser->consume();
                continue;
            }

            // Backslash
            $parser->consume();

            if ($parser->current !== null) {
                $href .= $parser->consume();
            }
        }

        return $href;
    }
}
