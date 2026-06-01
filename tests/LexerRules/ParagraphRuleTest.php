<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\Tokens\NewLineToken;
use Tempest\Markdown\Tokens\ParagraphToken;

class ParagraphRuleTest extends TestCase
{
    #[Test]
    public function test_single_line(): void
    {
        $token = new Lexer([new ParagraphRule()])->lex("Hello, world!\n")[0];

        $this->assertEquals(new ParagraphToken("Hello, world!\n"), $token);
    }

    #[Test]
    public function test_multi_line_paragraph(): void
    {
        $token = new Lexer([new ParagraphRule()])->lex("First line\nSecond line\n")[0];

        $this->assertEquals(new ParagraphToken("First line\nSecond line\n"), $token);
    }

    #[Test]
    public function test_stops_at_blank_line(): void
    {
        $tokens = new Lexer([new NewLineRule(), new ParagraphRule()])->lex("First\nSecond\n\nThird");

        $this->assertEquals(new ParagraphToken("First\nSecond"), $tokens[0]);
        $this->assertEquals(new NewLineToken("\n\n"), $tokens[1]);
        $this->assertEquals(new ParagraphToken('Third'), $tokens[2]);
    }

    #[Test]
    public function test_paragraph_without_trailing_newline(): void
    {
        $token = new Lexer([new ParagraphRule()])->lex('Hello, world!')[0];

        $this->assertEquals(new ParagraphToken('Hello, world!'), $token);
    }
}
