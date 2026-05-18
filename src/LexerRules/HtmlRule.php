<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\HtmlToken;

final readonly class HtmlRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('<');
    }

    public function lex(Lexer $lexer): Token
    {
        $voidTags = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

        $tagOpen = $lexer->consume() . $lexer->consumeWhile(Lexer::WHITESPACE);
        $tagName = $lexer->consumeUntil(' >');
        $tagClose = $lexer->consumeIncluding('>');
        $openingTag = $tagOpen . $tagName . $tagClose;

        if (in_array(strtolower($tagName), $voidTags, strict: true)) {
            return new HtmlToken($openingTag);
        }

        // Self-closing tags (<img />, <br />) need no closing tag.
        if (str_ends_with($openingTag, '/>')) {
            return new HtmlToken($openingTag . $lexer->consumeWhile(Lexer::NEW_LINE));
        }

        // Extract tag name from opening tag: "<div class..." → "div".
        $tagName = substr($openingTag, 1, strcspn($openingTag, " \t\n\r/>", 1));

        $content = $openingTag;
        $depth = 1;

        // Track nesting depth for the same tag name so that e.g.
        // <div><div>…</div></div> is consumed as a single token.
        while ($depth > 0 && $lexer->current !== null) {
            // Bulk-skip to '<' on each iteration so comesNext is only called
            // at actual tag boundaries, not character-by-character.
            $content .= $lexer->consumeUntil('<');

            if ($lexer->current === null) {
                break;
            }

            if ($lexer->comesNext("</{$tagName}")) {
                $depth--;
                $content .= $lexer->consumeIncluding('>');
            } elseif ($lexer->comesNext("<{$tagName}")) {
                $depth++;
                $content .= $lexer->consumeIncluding('>');
            } else {
                // Some other tag — consume the '<' and continue.
                $content .= $lexer->consume();
            }
        }

        $content .= $lexer->consumeWhile(Lexer::NEW_LINE);

        return new HtmlToken($content);
    }
}
