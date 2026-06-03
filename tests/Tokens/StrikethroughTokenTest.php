<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\StrikethroughToken;

class StrikethroughTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new StrikethroughToken('deleted');

        $this->assertEquals('<s>deleted</s>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $token = new StrikethroughToken('hello _world_');

        $this->assertEquals('<s>hello <em>world</em></s>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $token = new StrikethroughToken('hello **world**');

        $this->assertEquals('<s>hello <strong>world</strong></s>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new StrikethroughToken('hello [world](#)');

        $this->assertEquals('<s>hello <a href="#">world</a></s>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_and_italic_text(): void
    {
        $token = new StrikethroughToken('hello ***world***');

        $this->assertEquals('<s>hello <strong><em>world</em></strong></s>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<s><strong><em>text</em></strong></s>', new StrikethroughToken('***text***')->parse($parser));
        $this->assertEquals('<s><strong>text</strong></s>', new StrikethroughToken('**text**')->parse($parser));
        $this->assertEquals('<s><em>text</em></s>', new StrikethroughToken('*text*')->parse($parser));
        $this->assertEquals('<s><strong><em>text</em></strong></s>', new StrikethroughToken('___text___')->parse($parser));
        $this->assertEquals('<s><strong>text</strong></s>', new StrikethroughToken('__text__')->parse($parser));
        $this->assertEquals('<s><em>text</em></s>', new StrikethroughToken('_text_')->parse($parser));
    }
}
