<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\Tokens\QuoteToken;

class QuoteRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        /** @var QuoteToken $token */
        $token = new Lexer([new QuoteRule()])->lex('> quote')[0];

        $this->assertSame('quote', $token->content);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        /** @var QuoteToken $token */
        $token = new Lexer([new QuoteRule()])->lex(<<<'MD'
        > line 1
        > > line 2
        > line 3
        MD)[0];

        $this->assertSame(<<<'TXT'
        line 1
        > line 2
        line 3
        TXT, $token->content);
    }

    #[Test]
    public function test_lex_only_at_start_of_line(): void
    {
        $tokens = new Lexer([new QuoteRule()])->lex('two > one');

        $this->assertCount(0, $tokens);
    }
}
