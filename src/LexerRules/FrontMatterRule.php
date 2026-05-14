<?php

namespace Tempest\Markdown\LexerRules;

use Symfony\Component\Yaml\Yaml;
use Tempest\Markdown\Exceptions\FrontMatterCouldNotBeParsed;
use Tempest\Markdown\Exceptions\FrontMatterWasNotProperlyClosed;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;
use Tempest\Markdown\Tokens\FrontMatterToken;
use Throwable;

final readonly class FrontMatterRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        if ($lexer->position !== 0) {
            return false;
        }

        if (! $lexer->comesNext('---', 3)) {
            return false;
        }

        return true;
    }

    public function lex(Lexer $lexer): ?Token
    {
        $lexer->consumeWhile('-');
        $lexer->consumeWhile(Lexer::NEW_LINE);
        $content = $lexer->consumeUntilString('---');

        if (! $lexer->comesNext('---', 3)) {
            throw new FrontMatterWasNotProperlyClosed($content);
        }

        $lexer->consumeWhile('-');
        $lexer->consumeWhile(Lexer::NEW_LINE);

        try {
            $data = Yaml::parse($content);
        } catch (Throwable $cause) {
            throw new FrontMatterCouldNotBeParsed($content, $cause);
        }

        return new FrontMatterToken($data);
    }
}
