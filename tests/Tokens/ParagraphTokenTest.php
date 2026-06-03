<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\ParagraphToken;

class ParagraphTokenTest extends ParserTestCase
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
        $token = new ParagraphToken('Hello, _world_!');

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

    #[Test]
    public function test_parse_with_bold_and_italic(): void
    {
        $token = new ParagraphToken('Hello, ***world***!');

        $expectedHtml = '<p>Hello, <strong><em>world</em></strong>!</p>';

        $actualHtml = $token->parse(new Parser());

        $this->assertEquals($expectedHtml, $actualHtml);
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<p><strong><em>text</em></strong></p>', new ParagraphToken('***text***')->parse($parser));
        $this->assertEquals('<p><strong>text</strong></p>', new ParagraphToken('**text**')->parse($parser));
        $this->assertEquals('<p><em>text</em></p>', new ParagraphToken('*text*')->parse($parser));
        $this->assertEquals('<p><strong><em>text</em></strong></p>', new ParagraphToken('___text___')->parse($parser));
        $this->assertEquals('<p><strong>text</strong></p>', new ParagraphToken('__text__')->parse($parser));
        $this->assertEquals('<p><em>text</em></p>', new ParagraphToken('_text_')->parse($parser));
    }
}
