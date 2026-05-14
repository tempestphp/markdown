<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ListTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function single_item(): void
    {
        $this->assertSame(
            '<ul><li>item</li></ul>',
            $this->parser->parse('- item')->html,
        );
    }

    #[Test]
    public function multiple_items(): void
    {
        $this->assertSame(
            '<ul><li>one</li><li>two</li><li>three</li></ul>',
            $this->parser->parse("- one\n- two\n- three")->html,
        );
    }

    #[Test]
    public function item_with_bold(): void
    {
        $this->assertSame(
            '<ul><li>hello <strong>world</strong></li></ul>',
            $this->parser->parse('- hello **world**')->html,
        );
    }

    #[Test]
    public function item_with_italic(): void
    {
        $this->assertSame(
            '<ul><li>hello <em>world</em></li></ul>',
            $this->parser->parse('- hello __world__')->html,
        );
    }

    #[Test]
    public function item_with_link(): void
    {
        $this->assertSame(
            '<ul><li><a href="#">world</a></li></ul>',
            $this->parser->parse('- [world](#)')->html,
        );
    }

    #[Test]
    public function item_with_inline_code(): void
    {
        $this->assertSame(
            '<ul><li>run <code>php tempest</code></li></ul>',
            $this->parser->parse('- run `php tempest`')->html,
        );
    }

    #[Test]
    public function nested_single_child(): void
    {
        $this->assertSame(
            '<ul><li>parent<ul><li>child</li></ul></li></ul>',
            $this->parser->parse("- parent\n  - child")->html,
        );
    }

    #[Test]
    public function nested_multiple_children(): void
    {
        $this->assertSame(
            '<ul><li>parent<ul><li>child one</li><li>child two</li></ul></li></ul>',
            $this->parser->parse("- parent\n  - child one\n  - child two")->html,
        );
    }

    #[Test]
    public function nested_sibling_after_sublist(): void
    {
        $this->assertSame(
            '<ul><li>one<ul><li>child</li></ul></li><li>two</li></ul>',
            $this->parser->parse("- one\n  - child\n- two")->html,
        );
    }

    #[Test]
    public function three_levels_of_nesting(): void
    {
        $this->assertSame(
            '<ul><li>a<ul><li>b<ul><li>c</li></ul></li></ul></li></ul>',
            $this->parser->parse("- a\n  - b\n    - c")->html,
        );
    }

    #[Test]
    public function bare_dash_followed_by_digit_is_paragraph_not_list(): void
    {
        // Bug fix vs. the original ListRule, which triggered on any `-`.
        $this->assertSame(
            '<p>-2 is negative two</p>',
            $this->parser->parse('-2 is negative two')->html,
        );
    }

    #[Test]
    public function dash_with_no_space_is_paragraph(): void
    {
        $this->assertSame(
            '<p>-foo</p>',
            $this->parser->parse('-foo')->html,
        );
    }

    #[Test]
    public function thin_ruler_still_works(): void
    {
        // Three dashes must remain a horizontal rule, not a list of '--'.
        $this->assertSame(
            "<p>x\n</p><hr/>",
            $this->parser->parse("x\n---")->html,
        );
    }
}
