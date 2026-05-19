<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\StrikethroughRule;

class StrikethroughRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<s>strikethrough</s>', (string) new Parser([new StrikethroughRule()])->parse('~~strikethrough~~'));
    }

    #[Test]
    public function test_lex_single_tilde(): void
    {
        $this->assertSame('<s>strikethrough</s>', (string) new Parser([new StrikethroughRule()])->parse('~strikethrough~'));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $this->assertSame('<s>hello <em>world</em></s>', (string) new Parser([new StrikethroughRule()])->parse('~~hello __world__~~'));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $this->assertSame('<s>hello <strong>world</strong></s>', (string) new Parser([new StrikethroughRule()])->parse('~~hello **world**~~'));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $this->assertSame('<s>hello <a href="#">world</a></s>', (string) new Parser([new StrikethroughRule()])->parse('~~hello [world](#)~~'));
    }
}
