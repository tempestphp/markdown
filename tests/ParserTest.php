<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Exceptions\MaximumNestingDepthWasExceeded;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Rules\ParagraphRule;

final class ParserTest extends ParserTestCase
{
    #[Test]
    public function test_lex_snippet(): void
    {
        $html = (string) new Parser()->parse(<<<'MD'
        # Test
        Hello **world**
        MD);

        $this->assertStringContainsString('<h1 id="test">Test</h1>', $html);
        $this->assertStringContainsString('<p>Hello <strong>world</strong></p>', $html);
    }

    #[Test]
    public function test_lookahead(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        | Test |
        | ---- |
        | Hello |
        MD);

        $result = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

        $this->assertCount(2, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame("| ---- |\n", $result[1]);
        $this->assertSame(0, $parser->position);
    }

    #[Test]
    public function test_lookahead_with_mismatches(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        | Test |

        MD);

        $result = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

        $this->assertCount(1, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame(0, $parser->position);
    }

    #[Test]
    public function test_lookahead_without_match(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        ABC
        MD);

        $result = $parser->lookaheadUntil('D');

        $this->assertEmpty($result);
    }

    #[Test]
    public function test_remove_rules_removes_rule(): void
    {
        $html = (string) new Parser()
            ->removeRules(HeadingRule::class)
            ->parse('# Not a heading');

        $this->assertStringNotContainsString('<h1', $html);
    }

    #[Test]
    public function test_remove_rules_does_not_affect_other_rules(): void
    {
        $html = (string) new Parser()
            ->removeRules(HeadingRule::class)
            ->parse("# Not a heading\n\nStill a paragraph");

        $this->assertStringNotContainsString('<h1', $html);
        $this->assertStringContainsString('<p>Still a paragraph</p>', $html);
    }

    #[Test]
    public function test_remove_multiple_rules(): void
    {
        $html = (string) new Parser()
            ->removeRules(HeadingRule::class, ParagraphRule::class)
            ->parse("# Heading\n\nParagraph");

        $this->assertStringNotContainsString('<h1', $html);
        $this->assertStringNotContainsString('<p>', $html);
    }

    #[Test]
    public function test_remove_rules_returns_clone(): void
    {
        $parser = new Parser();
        $modified = $parser->removeRules(HeadingRule::class);

        $this->assertNotSame($parser, $modified);
        $this->assertContains(HeadingRule::class, array_map(fn ($r) => $r::class, $parser->rules));
        $this->assertNotContains(HeadingRule::class, array_map(fn ($r) => $r::class, $modified->rules));
    }

    #[Test]
    public function test_comes_next_with_offset(): void
    {
        $parser = new Parser()->setContent('**__');

        $this->assertTrue($parser->comesNext('*'));
        $this->assertTrue($parser->comesNext('*', offset: 1));
        $this->assertFalse($parser->comesNext('*', offset: 2));
        $this->assertTrue($parser->comesNext('_', offset: 2));
        $this->assertFalse($parser->comesNext('_', offset: 10));
    }

    #[Test]
    public function test_configuration_does_not_leak_between_instances(): void
    {
        $withHighlighter = new Parser();
        $withHighlighter->parse('`x`');

        $noHighlighter = new Parser(highlighter: null);

        $this->assertSame('<p><code>&lt;b&gt;x&lt;/b&gt;</code></p>', $noHighlighter->parse('`<b>x</b>`')->html);
    }

    #[Test]
    public function test_max_nesting_depth_limits_nested_tokens(): void
    {
        $this->expectException(MaximumNestingDepthWasExceeded::class);

        new Parser(maxNestingDepth: 3)->parse('Hello **world**');
    }

    #[Test]
    public function test_max_nesting_depth_limits_nested_lists(): void
    {
        $this->expectException(MaximumNestingDepthWasExceeded::class);

        new Parser(maxNestingDepth: 3)->parse("- one\n  - two\n    - three");
    }

    #[Test]
    public function test_default_max_nesting_depth_allows_normal_content(): void
    {
        $html = (string) new Parser()->parse("Hello **bold** and **more bold**\n\n- one\n  - two");

        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<li>', $html);
    }
}
