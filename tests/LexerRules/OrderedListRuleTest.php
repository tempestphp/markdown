<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\OrderedListRule;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\OrderedListToken;

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
