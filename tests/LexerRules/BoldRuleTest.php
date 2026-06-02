<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\Tokens\BoldToken;

class BoldRuleTest extends TestCase
{
    #[Test]
    public function test_lex_double_asterisk(): void
    {
        $token = new Lexer([new BoldRule()])->lex('**bold**')[0];

        $this->assertEquals(new BoldToken('bold'), $token);
    }

    #[Test]
    public function test_lex_asterisk_with_underscore(): void
    {
        $token = new Lexer([new BoldRule()])->lex('**_bold_**')[0];

        $this->assertEquals(new BoldToken('_bold_'), $token);
    }

    #[Test]
    public function test_does_not_lex_single_asterisk(): void
    {
        $tokens = new Lexer([new BoldRule()])->lex('*bold*');

        $this->assertCount(0, $tokens);
    }

    #[Test]
    public function test_lex_double_underscore(): void
    {
        $token = new Lexer([new BoldRule()])->lex('__bold__')[0];

        $this->assertEquals(new BoldToken('bold'), $token);
    }

    #[Test]
    public function test_does_not_lex_single_underscore(): void
    {
        $tokens = new Lexer([new BoldRule()])->lex('_bold_');

        $this->assertCount(0, $tokens);
    }

    #[Test]
    public function test_underscore_with_asterisk(): void
    {
        $token = new Lexer([new BoldRule()])->lex('__*bold*__')[0];

        $this->assertEquals(new BoldToken('*bold*'), $token);
    }
}
