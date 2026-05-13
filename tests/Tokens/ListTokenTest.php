<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\ListItem;
use Tempest\Markdown\Tokens\ListToken;

class ListTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new ListToken([new ListItem('item')]);

        $this->assertEquals('<ul><li>item</li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_multiple_items(): void
    {
        $token = new ListToken([new ListItem('one'), new ListItem('two'), new ListItem('three')]);

        $this->assertEquals('<ul><li>one</li><li>two</li><li>three</li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new ListToken([new ListItem('hello **world**')]);

        $this->assertEquals('<ul><li>hello <strong>world</strong></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new ListToken([new ListItem('hello __world__')]);

        $this->assertEquals('<ul><li>hello <em>world</em></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new ListToken([new ListItem('[world](#)')]);

        $this->assertEquals('<ul><li><a href="#">world</a></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new ListToken([new ListItem('run `php artisan`')]);

        $this->assertEquals('<ul><li>run <code>php artisan</code></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_nested(): void
    {
        $token = new ListToken([
            new ListItem('parent', new ListToken([
                new ListItem('child'),
            ])),
        ]);

        $this->assertEquals('<ul><li>parent<ul><li>child</li></ul></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_nested_multiple_children(): void
    {
        $token = new ListToken([
            new ListItem('parent', new ListToken([
                new ListItem('child one'),
                new ListItem('child two'),
            ])),
        ]);

        $this->assertEquals('<ul><li>parent<ul><li>child one</li><li>child two</li></ul></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_nested_sibling_after_sublist(): void
    {
        $token = new ListToken([
            new ListItem('one', new ListToken([
                new ListItem('child'),
            ])),
            new ListItem('two'),
        ]);

        $this->assertEquals('<ul><li>one<ul><li>child</li></ul></li><li>two</li></ul>', $token->parse(new Parser()));
    }
}
