<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\ItalicToken;

class ItalicTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new ItalicToken('world');

        $this->assertEquals('<em>world</em>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $token = new ItalicToken('hello **world**');

        $this->assertEquals('<em>hello <strong>world</strong></em>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $token = new ItalicToken('hello ~~world~~');

        $this->assertEquals('<em>hello <s>world</s></em>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new ItalicToken('hello [world](#)');

        $this->assertEquals('<em>hello <a href="#">world</a></em>', $token->parse(new Parser()));
    }
}
