<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\HtmlToken;

class HtmlTokenTest extends ParserTestCase
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
        $token = new HtmlToken('<p>Hello, _world_!</p>');

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

    #[Test]
    public function test_parse_with_bold_and_italic(): void
    {
        $token = new HtmlToken('<p>Hello, ***world***!</p>');

        $this->assertSame('<p>Hello, <strong><em>world</em></strong>!</p>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertSame('<p><strong><em>text</em></strong></p>', new HtmlToken('<p>***text***</p>')->parse($parser));
        $this->assertSame('<p><strong>text</strong></p>', new HtmlToken('<p>**text**</p>')->parse($parser));
        $this->assertSame('<p><em>text</em></p>', new HtmlToken('<p>*text*</p>')->parse($parser));
        $this->assertSame('<p><strong><em>text</em></strong></p>', new HtmlToken('<p>___text___</p>')->parse($parser));
        $this->assertSame('<p><strong>text</strong></p>', new HtmlToken('<p>__text__</p>')->parse($parser));
        $this->assertSame('<p><em>text</em></p>', new HtmlToken('<p>_text_</p>')->parse($parser));
    }
}
