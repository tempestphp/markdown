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
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse(
            "- item\n",
        );

        $this->assertSame('<ul><li>item</li></ul>', $html);
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse(
            "- one\n- two\n",
        );

        $this->assertSame('<ul><li>one</li><li>two</li></ul>', $html);
    }

    #[Test]
    public function test_asterisk_and_plus_markers(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ]);

        $this->assertSame(
            '<ul><li>one</li><li>two</li></ul>',
            (string) $parser->parse("* one\n* two"),
        );
        $this->assertSame(
            '<ul><li>one</li><li>two</li></ul>',
            (string) $parser->parse("+ one\n+ two"),
        );
    }

    #[Test]
    public function test_lazy_continuation(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ]);

        $this->assertSame(
            '<ul><li>one continued</li><li>two</li></ul>',
            (string) $parser->parse("- one\ncontinued\n- two"),
        );
        $this->assertSame(
            '<ul><li>one continued</li></ul>',
            (string) $parser->parse("* one\ncontinued"),
        );
    }

    #[Test]
    public function test_lazy_continuation_preserves_following_blocks(): void
    {
        $parser = new Parser(highlighter: null);

        $this->assertSame(
            "<ul><li>one</li></ul><hr/>\n<p>after</p>",
            (string) $parser->parse("- one\n---\nafter"),
        );
        $this->assertSame(
            '<ul><li>one</li></ul><div>block</div>',
            (string) $parser->parse("- one\n<div>block</div>"),
        );
    }

    #[Test]
    public function test_nested_list_after_lazy_continuation(): void
    {
        $parser = new Parser(highlighter: null);

        foreach (['-', '*', '+'] as $marker) {
            $this->assertSame(
                '<ul><li>one continued<ul><li>child</li></ul></li><li>two</li></ul>',
                (string) $parser->parse(
                    "{$marker} one\ncontinued\n  {$marker} child\n{$marker} two",
                ),
            );
        }
    }

    #[Test]
    public function test_lex_multiline_items(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse(
            "- one\n   continued\n   further\n- two\n",
        );

        $this->assertSame(
            '<ul><li>one continued further</li><li>two</li></ul>',
            $html,
        );
    }

    #[Test]
    public function test_hyphen_without_whitespace_is_not_a_list(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse('-not list');

        $this->assertSame('-not list', $html);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse(
            "- parent\n  - child\n",
        );

        $this->assertSame(
            '<ul><li>parent<ul><li>child</li></ul></li></ul>',
            $html,
        );
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse(
            "- parent\n  - child one\n  - child two\n",
        );

        $this->assertSame(
            '<ul><li>parent<ul><li>child one</li><li>child two</li></ul></li></ul>',
            $html,
        );
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ListRule(),
            new TextRule(),
        ])->parse(
            "- one\n  - child\n- two\n",
        );

        $this->assertSame(
            '<ul><li>one<ul><li>child</li></ul></li><li>two</li></ul>',
            $html,
        );
    }
}
