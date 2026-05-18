<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ListRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\ListToken;
use Tempest\Markdown\Tokens\TextToken;

class ListRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ListRule()])->lex("- item\n")[0];

        $this->assertEquals(new ListToken([new ListItem('item')]), $token);
    }

    #[Test]
    public function test_lex_multiple_items(): void
    {
        $token = new Lexer([new ListRule()])->lex("- one\n- two\n")[0];

        $this->assertEquals(new ListToken([new ListItem('one'), new ListItem('two')]), $token);
    }

    #[Test]
    public function test_hyphen_without_whitespace_is_not_a_list(): void
    {
        $tokens = new Lexer([new ListRule(), new TextRule()])->lex('-not list');

        $this->assertEquals(new TextToken('-not list'), $tokens[0]);
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $token = new Lexer([new ListRule()])->lex("- parent\n  - child\n")[0];

        $expected = new ListToken([
            new ListItem('parent', new ListToken([
                new ListItem('child'),
            ])),
        ]);

        $this->assertEquals($expected, $token);
    }

    #[Test]
    public function test_lex_nested_multiple_children(): void
    {
        $token = new Lexer([new ListRule()])->lex("- parent\n  - child one\n  - child two\n")[0];

        $expected = new ListToken([
            new ListItem('parent', new ListToken([
                new ListItem('child one'),
                new ListItem('child two'),
            ])),
        ]);

        $this->assertEquals($expected, $token);
    }

    #[Test]
    public function test_lex_nested_sibling_after_sublist(): void
    {
        $token = new Lexer([new ListRule()])->lex("- one\n  - child\n- two\n")[0];

        $expected = new ListToken([
            new ListItem('one', new ListToken([
                new ListItem('child'),
            ])),
            new ListItem('two'),
        ]);

        $this->assertEquals($expected, $token);
    }
}
