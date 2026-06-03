<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\ParserRules\OrderedListRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Parser;

class OrderedListRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse("1. item\n");

        $this->assertSame('<ol><li>item</li></ol>', $html);
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse("1. one\n2. two\n");

        $this->assertSame('<ol><li>one</li><li>two</li></ol>', $html);
    }

    #[Test]
    public function test_lex_multi_digit_numbers(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse("10. ten\n11. eleven\n");

        $this->assertSame('<ol><li>ten</li><li>eleven</li></ol>', $html);
    }

    #[Test]
    public function test_numeric_text_without_marker_is_not_an_ordered_list(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule(), new TextRule()])->parse('2026 is year');

        $this->assertSame('2026 is year', $html);
    }

    #[Test]
    public function test_ordered_list_marker_requires_whitespace_after_period(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule(), new TextRule()])->parse('1.not list');

        $this->assertSame('1.not list', $html);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse("1. parent\n  1. child\n");

        $this->assertSame('<ol><li>parent<ol><li>child</li></ol></li></ol>', $html);
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse("1. parent\n  1. child one\n  2. child two\n");

        $this->assertSame('<ol><li>parent<ol><li>child one</li><li>child two</li></ol></li></ol>', $html);
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse("1. one\n  1. child\n2. two\n");

        $this->assertSame('<ol><li>one<ol><li>child</li></ol></li><li>two</li></ol>', $html);
    }

    #[Test]
    public function test_only_numbers_are_allowed(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new OrderedListRule()])->parse('1a. one');

        $this->assertSame('', $html);
    }
}
