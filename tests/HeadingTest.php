<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class HeadingTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function h1(): void
    {
        $this->assertSame(
            '<h1 id="hello">Hello</h1>',
            $this->parser->parse('# Hello')->html,
        );
    }

    #[Test]
    public function h2(): void
    {
        $this->assertSame(
            '<h2 id="hello">Hello</h2>',
            $this->parser->parse('## Hello')->html,
        );
    }

    #[Test]
    public function h3(): void
    {
        $this->assertSame(
            '<h3 id="hello">Hello</h3>',
            $this->parser->parse('### Hello')->html,
        );
    }

    #[Test]
    public function h6(): void
    {
        $this->assertSame(
            '<h6 id="hello-world">Hello World</h6>',
            $this->parser->parse('###### Hello World')->html,
        );
    }

    #[Test]
    public function slug_lowercases_and_dashes_spaces(): void
    {
        $this->assertSame(
            '<h2 id="hello-world">Hello World</h2>',
            $this->parser->parse('## Hello World')->html,
        );
    }

    #[Test]
    public function heading_content_is_emitted_raw_without_inline_parsing(): void
    {
        // HeadingToken did not apply inline parsing to its content; markers render literally.
        $this->assertSame(
            '<h1 id="**bold**">**bold**</h1>',
            $this->parser->parse('# **bold**')->html,
        );
    }
}
