<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\HtmlCommentToken;

class HtmlCommentTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new HtmlCommentToken('<!-- comment -->');

        $this->assertSame('<!-- comment -->', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_multiline(): void
    {
        $comment = "<!--\nmultiline\ncomment\n-->";
        $token = new HtmlCommentToken($comment);

        $this->assertSame($comment, $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_does_not_process_markdown(): void
    {
        $token = new HtmlCommentToken('<!-- **bold** _italic_ -->');

        $this->assertSame('<!-- **bold** _italic_ -->', $token->parse(new Parser()));
    }
}
