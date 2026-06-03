<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\OrderedListToken;

class OrderedListTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new OrderedListToken([new ListItem('item')]);

        $this->assertEquals('<ol><li>item</li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_multiple_items(): void
    {
        $token = new OrderedListToken([new ListItem('one'), new ListItem('two'), new ListItem('three')]);

        $this->assertEquals('<ol><li>one</li><li>two</li><li>three</li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new OrderedListToken([new ListItem('hello **world**')]);

        $this->assertEquals('<ol><li>hello <strong>world</strong></li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new OrderedListToken([new ListItem('hello _world_')]);

        $this->assertEquals('<ol><li>hello <em>world</em></li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new OrderedListToken([new ListItem('[world](#)')]);

        $this->assertEquals('<ol><li><a href="#">world</a></li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new OrderedListToken([new ListItem('run `php tempest`')]);

        $this->assertEquals('<ol><li>run <code class="language-txt">php tempest</code></li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_nested(): void
    {
        $token = new OrderedListToken([
            new ListItem('parent', new OrderedListToken([
                new ListItem('child'),
            ])),
        ]);

        $this->assertEquals('<ol><li>parent<ol><li>child</li></ol></li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_nested_sibling_after_sublist(): void
    {
        $token = new OrderedListToken([
            new ListItem('one', new OrderedListToken([
                new ListItem('child'),
            ])),
            new ListItem('two'),
        ]);

        $this->assertEquals('<ol><li>one<ol><li>child</li></ol></li><li>two</li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_and_italic(): void
    {
        $token = new OrderedListToken([new ListItem('hello ***world***')]);

        $this->assertEquals('<ol><li>hello <strong><em>world</em></strong></li></ol>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<ol><li><strong><em>text</em></strong></li></ol>', new OrderedListToken([new ListItem('***text***')])->parse($parser));
        $this->assertEquals('<ol><li><strong>text</strong></li></ol>', new OrderedListToken([new ListItem('**text**')])->parse($parser));
        $this->assertEquals('<ol><li><em>text</em></li></ol>', new OrderedListToken([new ListItem('*text*')])->parse($parser));
        $this->assertEquals('<ol><li><strong><em>text</em></strong></li></ol>', new OrderedListToken([new ListItem('___text___')])->parse($parser));
        $this->assertEquals('<ol><li><strong>text</strong></li></ol>', new OrderedListToken([new ListItem('__text__')])->parse($parser));
        $this->assertEquals('<ol><li><em>text</em></li></ol>', new OrderedListToken([new ListItem('_text_')])->parse($parser));
    }
}
