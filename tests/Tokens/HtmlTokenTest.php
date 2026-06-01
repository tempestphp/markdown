<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\HtmlToken;

class HtmlTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new HtmlToken('<p>Hello</p>');

        $this->assertSame('<p>Hello</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new HtmlToken('<p>Hello, **world**!</p>');

        $this->assertSame('<p>Hello, <strong>world</strong>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough(): void
    {
        $token = new HtmlToken('<p>Hello, ~~world~~!</p>');

        $this->assertSame('<p>Hello, <s>world</s>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new HtmlToken('<p>Hello, __world__!</p>');

        $this->assertSame('<p>Hello, <em>world</em>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new HtmlToken('<p>Hello, [world](#)!</p>');

        $this->assertSame('<p>Hello, <a href="#">world</a>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $token = new HtmlToken('<p>Hello, ![world](#)!</p>');

        $this->assertSame('<p>Hello, <img src="#" alt="world">!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new HtmlToken('<p>Hello, `world`!</p>');

        $this->assertSame('<p>Hello, <code class="language-txt">world</code>!</p>', $token->parse(new Parser()));
    }
}
