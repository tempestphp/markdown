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
        $token = new Lexer([new QuoteRule()])->lex('> quote')[0];

        $this->assertEquals(new QuoteToken('quote', 1), $token);
    }

    #[Test]
    public function test_lex_deep_quote(): void
    {
        $token = new Lexer([new QuoteRule()])->lex('>>>> quote')[0];

        $this->assertEquals(new QuoteToken('quote', 4), $token);
    }
}
