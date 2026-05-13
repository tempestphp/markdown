<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\DivRule;
use Tempest\Markdown\Tokens\DivToken;

class DivRuleTest extends TestCase
{
    #[Test]
    public function test_lex_without_class(): void
    {
        $token = new Lexer([new DivRule()])->lex(":::\nHello\n:::\n")[0];

        $this->assertEquals(new DivToken(class: null, content: "Hello\n"), $token);
    }

    #[Test]
    public function test_lex_with_class(): void
    {
        $token = new Lexer([new DivRule()])->lex(":::warning\nHello\n:::\n")[0];

        $this->assertEquals(new DivToken(class: 'warning', content: "Hello\n"), $token);
    }

    #[Test]
    public function test_lex_with_multiple_classes(): void
    {
        $token = new Lexer([new DivRule()])->lex(":::foo bar\nHello\n:::\n")[0];

        $this->assertEquals(new DivToken(class: 'foo bar', content: "Hello\n"), $token);
    }

    #[Test]
    public function test_lex_multiline_content(): void
    {
        $token = new Lexer([new DivRule()])->lex(":::warning\nline one\nline two\n:::\n")[0];

        $this->assertEquals(new DivToken(class: 'warning', content: "line one\nline two\n"), $token);
    }
}
