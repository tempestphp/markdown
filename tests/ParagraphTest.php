<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ParagraphTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function plain_text(): void
    {
        $this->assertSame(
            '<p>Hello, world!</p>',
            $this->parser->parse('Hello, world!')->html,
        );
    }

    #[Test]
    public function paragraph_with_bold(): void
    {
        $this->assertSame(
            '<p>Hello, <strong>world</strong>!</p>',
            $this->parser->parse('Hello, **world**!')->html,
        );
    }

    #[Test]
    public function paragraph_with_italic(): void
    {
        $this->assertSame(
            '<p>Hello, <em>world</em>!</p>',
            $this->parser->parse('Hello, __world__!')->html,
        );
    }

    #[Test]
    public function paragraph_with_link(): void
    {
        $this->assertSame(
            '<p>Hello, <a href="#">world</a>!</p>',
            $this->parser->parse('Hello, [world](#)!')->html,
        );
    }

    #[Test]
    public function paragraph_with_image(): void
    {
        $this->assertSame(
            '<p>Hello, <img src="#" alt="world">!</p>',
            $this->parser->parse('Hello, ![world](#)!')->html,
        );
    }

    #[Test]
    public function paragraph_with_inline_code(): void
    {
        $this->assertSame(
            '<p>Hello, <code>world</code>!</p>',
            $this->parser->parse('Hello, `world`!')->html,
        );
    }

    #[Test]
    public function paragraph_swallows_trailing_blank_lines(): void
    {
        // ParagraphRule consumes trailing newlines into the paragraph content,
        // so `<p>` includes the literal blank lines.
        $this->assertSame(
            "<p>Hello\n\n</p><p>World</p>",
            $this->parser->parse("Hello\n\nWorld")->html,
        );
    }
}
