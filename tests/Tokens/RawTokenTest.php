<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\RawToken;

class RawTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse_returns_content_as_is(): void
    {
        $token = new RawToken('<b>raw</b>');

        $this->assertSame('<b>raw</b>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_multiline(): void
    {
        $content = "line1\nline2";
        $token = new RawToken($content);

        $this->assertSame($content, $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_does_not_process_markdown(): void
    {
        $token = new RawToken('**bold** _italic_');

        $this->assertSame('**bold** _italic_', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_empty_string(): void
    {
        $token = new RawToken('');

        $this->assertSame('', $token->parse(new Parser()));
    }
}
