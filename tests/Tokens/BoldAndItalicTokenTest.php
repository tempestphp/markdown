<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\BoldAndItalicToken;

class BoldAndItalicTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new BoldAndItalicToken('world');

        $this->assertEquals('<strong><em>world</em></strong>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $token = new BoldAndItalicToken('hello ~~world~~');

        $this->assertEquals('<strong><em>hello <s>world</s></em></strong>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new BoldAndItalicToken('hello [world](#)');

        $this->assertEquals('<strong><em>hello <a href="#">world</a></em></strong>', $token->parse(new Parser()));
    }
}
