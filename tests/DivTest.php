<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class DivTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function without_class(): void
    {
        $this->assertSame(
            "<div>Hello\n</div>",
            $this->parser->parse(":::\nHello\n:::")->html,
        );
    }

    #[Test]
    public function with_class(): void
    {
        $this->assertSame(
            "<div class=\"warning\">Hello\n</div>",
            $this->parser->parse(":::warning\nHello\n:::")->html,
        );
    }

    #[Test]
    public function with_multiple_classes(): void
    {
        $this->assertSame(
            "<div class=\"foo bar\">Hello\n</div>",
            $this->parser->parse(":::foo bar\nHello\n:::")->html,
        );
    }

    #[Test]
    public function with_inline_bold(): void
    {
        $this->assertSame(
            "<div>Hello <strong>world</strong>\n</div>",
            $this->parser->parse(":::\nHello **world**\n:::")->html,
        );
    }

    #[Test]
    public function with_inline_italic(): void
    {
        $this->assertSame(
            "<div>Hello <em>world</em>\n</div>",
            $this->parser->parse(":::\nHello __world__\n:::")->html,
        );
    }

    #[Test]
    public function with_inline_link(): void
    {
        $this->assertSame(
            "<div>Hello <a href=\"#\">world</a>\n</div>",
            $this->parser->parse(":::\nHello [world](#)\n:::")->html,
        );
    }

    #[Test]
    public function multiline_content(): void
    {
        $this->assertSame(
            "<div class=\"warning\">line one\nline two\n</div>",
            $this->parser->parse(":::warning\nline one\nline two\n:::")->html,
        );
    }
}
