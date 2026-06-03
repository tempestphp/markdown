<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\Parser;

class ParagraphRuleTest extends TestCase
{
    #[Test]
    public function test_single_line(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ParagraphRule()])->parse("Hello, world!\n");

        $this->assertSame("<p>Hello, world!\n</p>", $html);
    }

    #[Test]
    public function test_multi_line_paragraph(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ParagraphRule()])->parse("First line\nSecond line\n");

        $this->assertSame("<p>First line\nSecond line\n</p>", $html);
    }

    #[Test]
    public function test_stops_at_blank_line(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule(), new ParagraphRule()])->parse("First\nSecond\n\nThird");

        $this->assertSame("<p>First\nSecond</p>\n\n<p>Third</p>", $html);
    }

    #[Test]
    public function test_paragraph_without_trailing_newline(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ParagraphRule()])->parse('Hello, world!');

        $this->assertSame('<p>Hello, world!</p>', $html);
    }
}
