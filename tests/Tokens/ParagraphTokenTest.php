<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\ParagraphToken;

class ParagraphTokenTest extends TestCase
{
    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new ParagraphToken('Hello, **world**!');

        $expectedHtml = '<p>Hello, <strong>world</strong>!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    #[Test]
    public function test_parse_with_strikethrough(): void
    {
        $token = new ParagraphToken('Hello, ~~world~~!');

        $expectedHtml = '<p>Hello, <s>world</s>!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new ParagraphToken('Hello, __world__!');

        $expectedHtml = '<p>Hello, <em>world</em>!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new ParagraphToken('Hello, [world](#)!');

        $expectedHtml = '<p>Hello, <a href="#">world</a>!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $token = new ParagraphToken('Hello, ![world](#)!');

        $expectedHtml = '<p>Hello, <img src="#" alt="world">!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new ParagraphToken('Hello, `world`!');

        $expectedHtml = '<p>Hello, <code class="language-txt">world</code>!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }
}
