<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\HeadingToken;

class HeadingTokenTest extends TestCase
{
    #[Test]
    public function test_parse_h1(): void
    {
        $token = new HeadingToken('Hello', 1);

        $this->assertEquals('<h1 id="hello">Hello</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_h2(): void
    {
        $token = new HeadingToken('Hello', 2);

        $this->assertEquals('<h2 id="hello">Hello</h2>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_h6(): void
    {
        $token = new HeadingToken('Hello World', 6);

        $this->assertEquals('<h6 id="hello-world">Hello World</h6>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new HeadingToken('Hello, **world**!', 1);

        $this->assertEquals('<h1 id="hello,-**world**!">Hello, <strong>world</strong>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new HeadingToken('Hello, __world__!', 1);

        $this->assertEquals('<h1 id="hello,-__world__!">Hello, <em>world</em>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough(): void
    {
        $token = new HeadingToken('Hello, ~~world~~!', 1);

        $this->assertEquals('<h1 id="hello,-~~world~~!">Hello, <s>world</s>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new HeadingToken('Hello, [world](#)!', 1);

        $this->assertEquals('<h1 id="hello,-[world](#)!">Hello, <a href="#">world</a>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new HeadingToken('Hello, `world`!', 1);

        $this->assertEquals('<h1 id="hello,-`world`!">Hello, <code class="language-txt">world</code>!</h1>', $token->parse(new Parser()));
    }
}
