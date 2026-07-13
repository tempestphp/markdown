<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ListRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class ListRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule()])->parse("- item\n");

        $this->assertSame('<ul><li>item</li></ul>', $html);
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule()])->parse("- one\n- two\n");

        $this->assertSame('<ul><li>one</li><li>two</li></ul>', $html);
    }

    #[Test]
    public function test_lex_multiline_items(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule()])->parse("- one\n   continued\n   further\n- two\n");

        $this->assertSame('<ul><li>one continued further</li><li>two</li></ul>', $html);
    }

    #[Test]
    public function test_hyphen_without_whitespace_is_not_a_list(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule(), new TextRule()])->parse('-not list');

        $this->assertSame('-not list', $html);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule()])->parse("- parent\n  - child\n");

        $this->assertSame('<ul><li>parent<ul><li>child</li></ul></li></ul>', $html);
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule()])->parse("- parent\n  - child one\n  - child two\n");

        $this->assertSame('<ul><li>parent<ul><li>child one</li><li>child two</li></ul></li></ul>', $html);
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ListRule()])->parse("- one\n  - child\n- two\n");

        $this->assertSame('<ul><li>one<ul><li>child</li></ul></li><li>two</li></ul>', $html);
    }
}
