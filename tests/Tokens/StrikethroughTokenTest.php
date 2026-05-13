<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\StrikethroughToken;

class StrikethroughTokenTest extends TestCase
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
        $token = new StrikethroughToken('hello __world__');

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
}
