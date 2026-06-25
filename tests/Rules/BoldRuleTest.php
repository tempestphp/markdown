<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Tests\ParserTestCase;

class BoldRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_double_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('**bold**');

        $this->assertSame('<strong>bold</strong>', $html);
    }

    #[Test]
    public function test_double_asterisk_must_be_terminated(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule(), new BoldRule(), new ParagraphRule()])->parse("Hello**world\n\nHi");

        $this->assertSame("<p>Hello**world</p>\n\n<p>Hi</p>", $html);
    }

    #[Test]
    public function test_lex_asterisk_with_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule(), new ItalicRule()])->parse('**_bold_**');

        $this->assertSame('<strong><em>bold</em></strong>', $html);
    }

    #[Test]
    public function test_does_not_lex_single_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('*bold*');

        $this->assertSame('', $html);
    }

    #[Test]
    public function test_lex_double_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('__bold__');

        $this->assertSame('<strong>bold</strong>', $html);
    }

    #[Test]
    public function test_double_underscore_must_be_terminated(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule(), new BoldRule(), new ParagraphRule()])->parse("Hello__world\n\nHi");

        $this->assertSame("<p>Hello__world</p>\n\n<p>Hi</p>", $html);
    }

    #[Test]
    public function test_does_not_lex_single_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('_bold_');

        $this->assertSame('', $html);
    }

    #[Test]
    public function test_underscore_with_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('__*bold*__');

        $this->assertSame('<strong><em>bold</em></strong>', $html);
    }
}
