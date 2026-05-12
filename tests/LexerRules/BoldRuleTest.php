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
    public function test_lex(): void
    {
        $token = new Lexer([new BoldRule()])->lex('**bold**')[0];

        $this->assertEquals(new BoldToken('bold'), $token);
    }

    #[Test]
    public function test_lex_single_asterisk(): void
    {
        $token = new Lexer([new BoldRule()])->lex('*bold*')[0];

        $this->assertEquals(new BoldToken('bold'), $token);
    }
}
