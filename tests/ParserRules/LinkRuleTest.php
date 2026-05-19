<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\LinkRule;

class LinkRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<a href="#">click here</a>', (string) new Parser([new LinkRule()])->parse('[click here](#)'));
    }

    #[Test]
    public function test_lex_without_href(): void
    {
        $this->assertSame('<a href="">click here</a>', (string) new Parser([new LinkRule()])->parse('[click here]'));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $this->assertSame('<a href="#">click <strong>here</strong></a>', (string) new Parser([new LinkRule()])->parse('[click **here**](#)'));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $this->assertSame('<a href="#">click <em>here</em></a>', (string) new Parser([new LinkRule()])->parse('[click __here__](#)'));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $this->assertSame('<a href="#">click <s>here</s></a>', (string) new Parser([new LinkRule()])->parse('[click ~~here~~](#)'));
    }

    #[Test]
    public function test_parse_with_target_blank(): void
    {
        $this->assertSame(
            '<a href="https://tempestphp.com" target="_blank" rel="noopener noreferrer">click here</a>',
            (string) new Parser([new LinkRule()])->parse('[click here](*https://tempestphp.com)'),
        );
    }
}
