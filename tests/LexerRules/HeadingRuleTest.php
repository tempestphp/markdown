<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\Tokens\HeadingToken;

class HeadingRuleTest extends TestCase
{
    #[Test]
    public function test_lex_h1(): void
    {
        $token = new Lexer([new HeadingRule()])->lex('# Hello')[0];

        $this->assertEquals(new HeadingToken('Hello', 1), $token);
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $token = new Lexer([new HeadingRule()])->lex('### Hello')[0];

        $this->assertEquals(new HeadingToken('Hello', 3), $token);
    }
}
