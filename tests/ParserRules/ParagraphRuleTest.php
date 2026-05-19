<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ParagraphRule;

class ParagraphRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame("<p>Hello, world!\n</p>", (string) new Parser([new ParagraphRule()])->parse("Hello, world!\n"));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $this->assertSame('<p>Hello, <strong>world</strong>!</p>', (string) new Parser([new ParagraphRule()])->parse('Hello, **world**!'));
    }

    #[Test]
    public function test_parse_with_strikethrough(): void
    {
        $this->assertSame('<p>Hello, <s>world</s>!</p>', (string) new Parser([new ParagraphRule()])->parse('Hello, ~~world~~!'));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $this->assertSame('<p>Hello, <em>world</em>!</p>', (string) new Parser([new ParagraphRule()])->parse('Hello, __world__!'));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $this->assertSame('<p>Hello, <a href="#">world</a>!</p>', (string) new Parser([new ParagraphRule()])->parse('Hello, [world](#)!'));
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $this->assertSame('<p>Hello, <img src="#" alt="world">!</p>', (string) new Parser([new ParagraphRule()])->parse('Hello, ![world](#)!'));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $this->assertSame('<p>Hello, <code class="language-txt">world</code>!</p>', (string) new Parser([new ParagraphRule()])->parse('Hello, `world`!'));
    }
}
