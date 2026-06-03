<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\HtmlRule;
use Tempest\Markdown\ParserRules\NewLineRule;
use Tempest\Markdown\ParserRules\ParagraphRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HtmlRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HtmlRule()])->parse('<p>Hi</p>');

        $this->assertSame('<p>Hi</p>', $html);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HtmlRule()])->parse('<div><div>Hi</div></div>');

        $this->assertSame('<div><div>Hi</div></div>', $html);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $input = <<<'HTML'
        Hello

        <p>
        Hi
        </p>

        World
        HTML;

        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule(), new HtmlRule(), new ParagraphRule()])->parse($input);

        $this->assertStringContainsString('<p>Hello</p>', $html);
        $this->assertStringContainsString("<p>\nHi\n</p>", $html);
        $this->assertStringContainsString('<p>World</p>', $html);
    }

    #[Test]
    public function test_void_tags(): void
    {
        $input = '<area><base><br><col><embed><hr><img><input><link><meta><param><source><track><wbr>Hello';

        $html = (string) new Parser(highlighter: null, rules: [new HtmlRule(), new TextRule()])->parse($input);

        $this->assertSame($input, $html);
    }

    #[Test]
    public function test_void_tags_are_case_insensitive(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HtmlRule(), new NewLineRule(), new ParagraphRule()])->parse("<BR>\nHello");

        $this->assertSame("<BR>\n<p>Hello</p>", $html);
    }
}
