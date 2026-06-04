<?php

namespace Tempest\Markdown\Rules;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HtmlToken;

final class HtmlRule implements Rule, ProvidesFirstChar
{
    public string $firstChar = '<';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('<');
    }

    public function parse(Parser $parser): Token
    {
        $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

        $tagOpen = $parser->consume() . $parser->consumeWhile(Parser::WHITESPACE);
        $tagName = $parser->consumeUntil(' >');
        $tagClose = $parser->consumeIncluding('>');
        $openingTag = $tagOpen . $tagName . $tagClose;

        if (in_array(strtolower($tagName), $voidTags, strict: true)) {
            return new HtmlToken($openingTag);
        }

        // Self-closing tags (<img />, <br />) need no closing tag.
        if (str_ends_with($openingTag, '/>')) {
            return new HtmlToken($openingTag . $parser->consumeWhile(Parser::NEW_LINE));
        }

        // Extract tag name from opening tag: "<div class..." → "div".
        $tagName = substr($openingTag, 1, strcspn($openingTag, " \t\n\r/>", 1));

        $content = $openingTag;
        $depth = 1;

        // Track nesting depth for the same tag name so that e.g.
        // <div><div>…</div></div> is consumed as a single token.
        while ($depth > 0 && $parser->current !== null) {
            // Bulk-skip to '<' on each iteration so comesNext is only called
            // at actual tag boundaries, not character-by-character.
            $content .= $parser->consumeUntil('<');

            if ($parser->current === null) {
                break;
            }

            if ($parser->comesNext("</{$tagName}")) {
                $depth--;
                $content .= $parser->consumeIncluding('>');
            } elseif ($parser->comesNext("<{$tagName}")) {
                $depth++;
                $content .= $parser->consumeIncluding('>');
            } else {
                // Some other tag — consume the '<' and continue.
                $content .= $parser->consume();
            }
        }

        $content .= $parser->consumeWhile(Parser::NEW_LINE);

        return new HtmlToken($content);
    }
}
