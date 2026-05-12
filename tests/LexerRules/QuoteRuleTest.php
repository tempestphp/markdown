<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\QuoteRule;

class QuoteRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new QuoteRule()])->lex('> quote')[0];

        $this->assertSame('quote', $token->content);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
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
}
