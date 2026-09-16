<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\HtmlRule;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HtmlRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ])->parse(
            '<p>Hi</p>',
        );

        $this->assertSame('<p>Hi</p>', $html);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ])->parse(
            '<div><div>Hi</div></div>',
        );

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

        $html = (string) new Parser(highlighter: null, rules: [
            new NewLineRule(),
            new HtmlRule(),
            new ParagraphRule(),
            new TextRule(),
        ])->parse($input);

        $this->assertStringContainsString('<p>Hello</p>', $html);
        $this->assertStringContainsString("<p>\nHi\n</p>", $html);
        $this->assertStringContainsString('<p>World</p>', $html);
    }

    #[Test]
    public function test_void_tags(): void
    {
        $input = '<area><base><br><col><embed><hr><img><input><link><meta><param><source><track><wbr>Hello';

        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ])->parse($input);

        $this->assertSame($input, $html);
    }

    #[Test]
    public function test_void_tags_are_case_insensitive(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new NewLineRule(),
            new ParagraphRule(),
            new TextRule(),
        ])->parse("<BR>\nHello");

        $this->assertSame("<BR>\n<p>Hello</p>", $html);
    }

    #[Test]
    public function lex_keeps_script_content_raw(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ])->parse(
            '<script>const x = "**a**";</script>',
        );

        $this->assertSame('<script>const x = "**a**";</script>', $html);
    }

    #[Test]
    public function lex_keeps_style_content_raw(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ])->parse(
            '<style>a::after{content:"*x*"}</style>',
        );

        $this->assertSame('<style>a::after{content:"*x*"}</style>', $html);
    }

    #[Test]
    public function lex_keeps_pre_and_textarea_content_raw(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ]);

        $this->assertSame(
            '<pre>**a** _b_</pre>',
            (string) $parser->parse('<pre>**a** _b_</pre>'),
        );

        $this->assertSame(
            '<textarea>**a**</textarea>',
            (string) $parser->parse('<textarea>**a**</textarea>'),
        );
    }

    #[Test]
    public function lex_detects_raw_text_tags_case_insensitively(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new TextRule(),
        ])->parse(
            '<SCRIPT>**a**</SCRIPT>',
        );

        $this->assertSame('<SCRIPT>**a**</SCRIPT>', $html);
    }

    #[Test]
    public function lex_still_parses_markdown_in_other_elements(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HtmlRule(),
            new BoldRule(),
            new TextRule(),
        ])->parse('<div>**a**</div>');

        $this->assertSame('<div><strong>a</strong></div>', $html);
    }
}
