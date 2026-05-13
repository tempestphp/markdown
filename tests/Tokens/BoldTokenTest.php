<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\BoldToken;

class BoldTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new BoldToken('world');

        $this->assertEquals('<strong>world</strong>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $token = new BoldToken('hello __world__');

        $this->assertEquals('<strong>hello <em>world</em></strong>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $token = new BoldToken('hello ~~world~~');

        $this->assertEquals('<strong>hello <s>world</s></strong>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new BoldToken('hello [world](#)');

        $this->assertEquals('<strong>hello <a href="#">world</a></strong>', $token->parse(new Parser()));
    }
}
