<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\RawRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class RawRuleTest extends ParserTestCase
{
    #[Test]
    public function test_raw_content_is_passed_through(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new RawRule()])->parse('@@<b>raw</b>@@');

        $this->assertSame('<b>raw</b>', $html);
    }

    #[Test]
    public function test_raw_html_is_not_escaped(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new RawRule()])->parse('@@<script>alert("xss")</script>@@');

        $this->assertSame('<script>alert("xss")</script>', $html);
    }

    #[Test]
    public function test_raw_inline_with_surrounding_text(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new RawRule(), new TextRule()])->parse('Hello @@<em>world</em>@@ end');

        $this->assertSame('Hello <em>world</em> end', $html);
    }

    #[Test]
    public function test_multiple_raw_blocks(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new RawRule(), new TextRule()])->parse('@@foo@@ and @@bar@@');

        $this->assertSame('foo and bar', $html);
    }

    #[Test]
    public function test_raw_multiline_content(): void
    {
        $input = "@@line1\nline2@@";

        $html = (string) new Parser(highlighter: null, rules: [new RawRule()])->parse($input);

        $this->assertSame("line1\nline2", $html);
    }
}
