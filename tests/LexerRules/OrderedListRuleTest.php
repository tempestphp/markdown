<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\OrderedListRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\OrderedListToken;
use Tempest\Markdown\Tokens\TextToken;

class OrderedListRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new OrderedListRule()])->lex("1. item\n")[0];

        $this->assertEquals(new OrderedListToken([new ListItem('item')]), $token);
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $token = new Lexer([new OrderedListRule()])->lex("1. one\n2. two\n")[0];

        $this->assertEquals(new OrderedListToken([new ListItem('one'), new ListItem('two')]), $token);
    }

    #[Test]
    public function test_lex_multi_digit_numbers(): void
    {
        $token = new Lexer([new OrderedListRule()])->lex("10. ten\n11. eleven\n")[0];

        $this->assertEquals(new OrderedListToken([new ListItem('ten'), new ListItem('eleven')]), $token);
    }

    #[Test]
    public function test_numeric_text_without_marker_is_not_an_ordered_list(): void
    {
        $tokens = new Lexer([new OrderedListRule(), new TextRule()])->lex('2026 is year');

        $this->assertEquals(new TextToken('2026 is year'), $tokens[0]);
    }

    #[Test]
    public function test_ordered_list_marker_requires_whitespace_after_period(): void
    {
        $tokens = new Lexer([new OrderedListRule(), new TextRule()])->lex('1.not list');

        $this->assertEquals(new TextToken('1.not list'), $tokens[0]);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $token = new Lexer([new OrderedListRule()])->lex("1. parent\n  1. child\n")[0];

        $expected = new OrderedListToken([
            new ListItem('parent', new OrderedListToken([
                new ListItem('child'),
            ])),
        ]);

        $this->assertEquals($expected, $token);
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $token = new Lexer([new OrderedListRule()])->lex("1. parent\n  1. child one\n  2. child two\n")[0];

        $expected = new OrderedListToken([
            new ListItem('parent', new OrderedListToken([
                new ListItem('child one'),
                new ListItem('child two'),
            ])),
        ]);

        $this->assertEquals($expected, $token);
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $token = new Lexer([new OrderedListRule()])->lex("1. one\n  1. child\n2. two\n")[0];

        $expected = new OrderedListToken([
            new ListItem('one', new OrderedListToken([
                new ListItem('child'),
            ])),
            new ListItem('two'),
        ]);

        $this->assertEquals($expected, $token);
    }
}
