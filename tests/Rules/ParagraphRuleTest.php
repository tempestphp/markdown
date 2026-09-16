<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class ParagraphRuleTest extends ParserTestCase
{
    #[Test]
    public function test_single_line(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ParagraphRule(),
            new TextRule(),
        ])->parse(
            "Hello, world!\n",
        );

        $this->assertSame("<p>Hello, world!\n</p>", $html);
    }

    #[Test]
    public function test_multi_line_paragraph(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ParagraphRule(),
            new TextRule(),
        ])->parse(
            "First line\nSecond line\n",
        );

        $this->assertSame("<p>First line\nSecond line\n</p>", $html);
    }

    #[Test]
    public function test_stops_at_blank_line(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new NewLineRule(),
            new ParagraphRule(),
            new TextRule(),
        ])->parse("First\nSecond\n\nThird");

        $this->assertSame("<p>First\nSecond</p>\n\n<p>Third</p>", $html);
    }

    #[Test]
    public function test_paragraph_without_trailing_newline(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ParagraphRule(),
            new TextRule(),
        ])->parse(
            'Hello, world!',
        );

        $this->assertSame('<p>Hello, world!</p>', $html);
    }

    #[Test]
    public function test_setext_headings(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new ParagraphRule(),
            new TextRule(),
        ]);

        $this->assertSame(
            '<h1 id="first-heading">First heading</h1>',
            (string) $parser->parse("First heading\n===="),
        );
        $this->assertSame(
            '<h2 id="second-heading">Second heading</h2>',
            (string) $parser->parse("Second heading\n-----\n"),
        );
    }

    #[Test]
    public function test_setext_heading_can_span_multiple_lines(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new ParagraphRule(),
            new TextRule(),
        ]);

        $this->assertSame(
            "<h2 id=\"first-second\">First\nSecond</h2>",
            (string) $parser->parse("First\nSecond\n---"),
        );
    }

    #[Test]
    public function test_setext_heading_followed_by_paragraph(): void
    {
        $parser = new Parser(highlighter: null);

        $this->assertSame(
            '<h2 id="title">Title</h2><p>Body</p>',
            (string) $parser->parse("Title\n---\nBody"),
        );
    }

    #[Test]
    public function test_consecutive_setext_headings(): void
    {
        $parser = new Parser(highlighter: null);

        $this->assertSame(
            '<h1 id="one">One</h1><h2 id="two">Two</h2>',
            (string) $parser->parse("One\n===\nTwo\n---"),
        );
    }
}
