<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\HtmlRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Tokens\HtmlToken;
use Tempest\Markdown\Tokens\ParagraphToken;

class HtmlRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new HtmlRule()])->lex('<p>Hi</p>')[0];

        $this->assertEquals(new HtmlToken('<p>Hi</p>'), $token);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $token = new Lexer([new HtmlRule()])->lex('<div><div>Hi</div></div>')[0];

        $this->assertEquals(new HtmlToken('<div><div>Hi</div></div>'), $token);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $html = <<<'HTML'
        Hello        
        <p>
        Hi
        </p>
        World
        HTML;

        $tokens = new Lexer([new NewLineRule(), new HtmlRule(), new ParagraphRule()])->lex($html);

        $this->assertCount(3, $tokens);
        $this->assertEquals(new HtmlToken("<p>\nHi\n</p>\n"), $tokens[1]);
    }

    #[Test]
    public function test_void_tags(): void
    {
        $html = '<area><base><br><col><embed><hr><img><input><link><meta><param><source><track><wbr>Hello';

        $tokens = new Lexer([new HtmlRule(), new TextRule()])->lex($html);

        $this->assertCount(15, $tokens);
    }

    #[Test]
    public function test_void_tags_are_case_insensitive(): void
    {
        $tokens = new Lexer([new HtmlRule(), new NewLineRule(), new ParagraphRule()])->lex("<BR>\nHello");

        $this->assertCount(3, $tokens);
        $this->assertEquals(new HtmlToken('<BR>'), $tokens[0]);
        $this->assertEquals(new ParagraphToken('Hello'), $tokens[2]);
    }
}
