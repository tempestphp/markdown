<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\OrderedListRule;
use Tempest\Markdown\ParserRules\TextRule;

class OrderedListRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<ol><li>item</li></ol>', (string) new Parser([new OrderedListRule()])->parse("1. item\n"));
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $this->assertSame('<ol><li>one</li><li>two</li></ol>', (string) new Parser([new OrderedListRule()])->parse("1. one\n2. two\n"));
    }

    #[Test]
    public function test_lex_multi_digit_numbers(): void
    {
        $this->assertSame('<ol><li>ten</li><li>eleven</li></ol>', (string) new Parser([new OrderedListRule()])->parse("10. ten\n11. eleven\n"));
    }

    #[Test]
    public function test_numeric_text_without_marker_is_not_an_ordered_list(): void
    {
        $this->assertSame('2026 is year', (string) new Parser([new OrderedListRule(), new TextRule()])->parse('2026 is year'));
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $this->assertSame(
            '<ol><li>parent<ol><li>child</li></ol></li></ol>',
            (string) new Parser([new OrderedListRule()])->parse("1. parent\n  1. child\n"),
        );
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $this->assertSame(
            '<ol><li>parent<ol><li>child one</li><li>child two</li></ol></li></ol>',
            (string) new Parser([new OrderedListRule()])->parse("1. parent\n  1. child one\n  2. child two\n"),
        );
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $this->assertSame(
            '<ol><li>one<ol><li>child</li></ol></li><li>two</li></ol>',
            (string) new Parser([new OrderedListRule()])->parse("1. one\n  1. child\n2. two\n"),
        );
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $this->assertSame('<ol><li>hello <strong>world</strong></li></ol>', (string) new Parser([new OrderedListRule()])->parse("1. hello **world**\n"));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $this->assertSame('<ol><li>hello <em>world</em></li></ol>', (string) new Parser([new OrderedListRule()])->parse("1. hello __world__\n"));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $this->assertSame('<ol><li><a href="#">world</a></li></ol>', (string) new Parser([new OrderedListRule()])->parse("1. [world](#)\n"));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $this->assertSame('<ol><li>run <code class="language-txt">php tempest</code></li></ol>', (string) new Parser([new OrderedListRule()])->parse("1. run `php tempest`\n"));
    }
}
