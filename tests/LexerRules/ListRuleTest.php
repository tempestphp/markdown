<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ListRule;
use Tempest\Markdown\Tokens\ListToken;

class ListRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ListRule()])->lex("- item\n")[0];

        $this->assertEquals(new ListToken(['item']), $token);
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $token = new Lexer([new ListRule()])->lex("- one\r\n- two\r\n")[0];

        $this->assertEquals(new ListToken(['one', 'two']), $token);
    }
}
