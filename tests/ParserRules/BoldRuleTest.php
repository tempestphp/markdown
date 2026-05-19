<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;

class BoldRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<strong>bold</strong>', (string) new Parser([new BoldRule()])->parse('**bold**'));
    }

    #[Test]
    public function test_lex_single_asterisk(): void
    {
        $this->assertSame('<strong>bold</strong>', (string) new Parser([new BoldRule()])->parse('*bold*'));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $this->assertSame('<strong>hello <em>world</em></strong>', (string) new Parser([new BoldRule()])->parse('**hello __world__**'));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $this->assertSame('<strong>hello <s>world</s></strong>', (string) new Parser([new BoldRule()])->parse('**hello ~~world~~**'));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $this->assertSame('<strong>hello <a href="#">world</a></strong>', (string) new Parser([new BoldRule()])->parse('**hello [world](#)**'));
    }
}
