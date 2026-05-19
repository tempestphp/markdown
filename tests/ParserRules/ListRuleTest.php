<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ListRule;
use Tempest\Markdown\ParserRules\TextRule;

class ListRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<ul><li>item</li></ul>', (string) new Parser([new ListRule()])->parse("- item\n"));
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $this->assertSame('<ul><li>one</li><li>two</li></ul>', (string) new Parser([new ListRule()])->parse("- one\n- two\n"));
    }

    #[Test]
    public function test_hyphen_without_whitespace_is_not_a_list(): void
    {
        $this->assertSame('-not list', (string) new Parser([new ListRule(), new TextRule()])->parse('-not list'));
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $this->assertSame(
            '<ul><li>parent<ul><li>child</li></ul></li></ul>',
            (string) new Parser([new ListRule()])->parse("- parent\n  - child\n"),
        );
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $this->assertSame(
            '<ul><li>parent<ul><li>child one</li><li>child two</li></ul></li></ul>',
            (string) new Parser([new ListRule()])->parse("- parent\n  - child one\n  - child two\n"),
        );
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $this->assertSame(
            '<ul><li>one<ul><li>child</li></ul></li><li>two</li></ul>',
            (string) new Parser([new ListRule()])->parse("- one\n  - child\n- two\n"),
        );
    }
}
