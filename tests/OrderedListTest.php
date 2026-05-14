<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class OrderedListTest extends TestCase
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
            '<ol><li>item</li></ol>',
            $this->parser->parse('1. item')->html,
        );
    }

    #[Test]
    public function multiple_items(): void
    {
        $this->assertSame(
            '<ol><li>one</li><li>two</li><li>three</li></ol>',
            $this->parser->parse("1. one\n2. two\n3. three")->html,
        );
    }

    #[Test]
    public function multi_digit_marker(): void
    {
        $this->assertSame(
            '<ol><li>tenth</li></ol>',
            $this->parser->parse('10. tenth')->html,
        );
    }

    #[Test]
    public function item_with_bold(): void
    {
        $this->assertSame(
            '<ol><li>hello <strong>world</strong></li></ol>',
            $this->parser->parse('1. hello **world**')->html,
        );
    }

    #[Test]
    public function item_with_link(): void
    {
        $this->assertSame(
            '<ol><li><a href="#">world</a></li></ol>',
            $this->parser->parse('1. [world](#)')->html,
        );
    }

    #[Test]
    public function nested(): void
    {
        $this->assertSame(
            '<ol><li>parent<ol><li>child</li></ol></li></ol>',
            $this->parser->parse("1. parent\n  2. child")->html,
        );
    }

    #[Test]
    public function nested_sibling_after_sublist(): void
    {
        $this->assertSame(
            '<ol><li>one<ol><li>child</li></ol></li><li>two</li></ol>',
            $this->parser->parse("1. one\n  2. child\n3. two")->html,
        );
    }

    #[Test]
    public function decimal_number_is_paragraph_not_list(): void
    {
        // Bug fix vs. the original OrderedListRule, which triggered on any digit.
        $this->assertSame(
            '<p>1.5 is a number</p>',
            $this->parser->parse('1.5 is a number')->html,
        );
    }

    #[Test]
    public function digit_followed_by_dot_no_space_is_paragraph(): void
    {
        $this->assertSame(
            '<p>1.foo</p>',
            $this->parser->parse('1.foo')->html,
        );
    }

    #[Test]
    public function digit_with_no_dot_is_paragraph(): void
    {
        $this->assertSame(
            '<p>1 item</p>',
            $this->parser->parse('1 item')->html,
        );
    }
}
