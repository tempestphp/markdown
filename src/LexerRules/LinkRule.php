<?php

namespace Tempest\Markdown\LexerRules;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\ProvidesStopChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\LinkToken;

final class LinkRule implements Rule, ProvidesStopChar
{
    private(set) string $stopChar = '[';

    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('[', 1);
    }

    public function lex(Lexer $lexer): Token
    {
        $lexer->consumeIncluding('[');
        $content = $this->consumeContent($lexer);
        $lexer->consumeIncluding(']');

        $href = null;

        if ($lexer->comesNext('(', 1)) {
            $lexer->consumeIncluding('(');
            $href = $lexer->consumeUntil(')');
            $lexer->consumeIncluding(')');
        }

        return new LinkToken($content, $href);
    }

    private function consumeContent(Lexer $lexer): string
    {
        $content = '';
        $bracketDepth = 0;

        while ($lexer->current !== null) {
            if ($lexer->comesNext(']') && $bracketDepth === 0) {
                break;
            }

            if ($lexer->comesNext('[')) {
                $bracketDepth += 1;
            } elseif ($lexer->comesNext(']')) {
                $bracketDepth -= 1;
            }

            $content .= $lexer->consume();
        }

        return $content;
    }
}
