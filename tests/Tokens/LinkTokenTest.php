<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\LinkToken;

class LinkTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new LinkToken('click here', '#');

        $this->assertEquals('<a href="#">click here</a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $token = new LinkToken('click **here**', '#');

        $this->assertEquals('<a href="#">click <strong>here</strong></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $token = new LinkToken('click __here__', '#');

        $this->assertEquals('<a href="#">click <em>here</em></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $token = new LinkToken('click ~~here~~', '#');

        $this->assertEquals('<a href="#">click <s>here</s></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $token = new LinkToken('![alt](/image.jpg)', '/link');

        $this->assertEquals('<a href="/link"><img src="/image.jpg" alt="alt"></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_target_blank_text(): void
    {
        $token = new LinkToken('click here', '*https://tempestphp.com');

        $this->assertEquals('<a href="https://tempestphp.com" target="_blank" rel="noopener noreferrer">click here</a>', $token->parse(new Parser()));
    }
}
