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

        $this->assertEquals('<p>Hello, <strong>world</strong>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough(): void
    {
        $token = new ParagraphToken('Hello, ~~world~~!');

        $this->assertEquals('<p>Hello, <s>world</s>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new ParagraphToken('Hello, __world__!');

        $this->assertEquals('<p>Hello, <em>world</em>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new ParagraphToken('Hello, [world](#)!');

        $this->assertEquals('<p>Hello, <a href="#">world</a>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $token = new ParagraphToken('Hello, ![world](#)!');

        $this->assertEquals('<p>Hello, <img src="#" alt="world">!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new ParagraphToken('Hello, `world`!');

        $this->assertEquals('<p>Hello, <code class="language-txt">world</code>!</p>', $token->parse(new Parser()));
    }
}
