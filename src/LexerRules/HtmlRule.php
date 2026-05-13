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
        $openingTag = $lexer->consumeIncluding('>');

        if (str_ends_with($openingTag, '/>')) {
            return new HtmlToken($openingTag . $lexer->consumeWhile(Lexer::NEW_LINE));
        }

        $tagName = substr($openingTag, 1, strcspn($openingTag, " \t\n\r/>", 1));

        $content = $openingTag;
        $depth = 1;

        while ($depth > 0 && $lexer->current !== null) {
            if ($lexer->comesNext("<{$tagName}")) {
                $depth++;
                $content .= $lexer->consumeIncluding('>');
            } elseif ($lexer->comesNext("</{$tagName}")) {
                $depth--;
                $content .= $lexer->consumeIncluding('>');
            } else {
                $content .= $lexer->consume();
            }
        }

        $content .= $lexer->consumeWhile(Lexer::NEW_LINE);

        return new HtmlToken($content);
    }
}
