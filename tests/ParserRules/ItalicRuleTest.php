<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ItalicRule;

class ItalicRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<em>italic</em>', (string) new Parser([new ItalicRule()])->parse('__italic__'));
    }

    #[Test]
    public function test_lex_single_underscore(): void
    {
        $this->assertSame('<em>italic</em>', (string) new Parser([new ItalicRule()])->parse('_italic_'));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $this->assertSame('<em>hello <strong>world</strong></em>', (string) new Parser([new ItalicRule()])->parse('__hello **world**__'));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $this->assertSame('<em>hello <s>world</s></em>', (string) new Parser([new ItalicRule()])->parse('__hello ~~world~~__'));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $this->assertSame('<em>hello <a href="#">world</a></em>', (string) new Parser([new ItalicRule()])->parse('__hello [world](#)__'));
    }
}
