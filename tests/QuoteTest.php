<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class QuoteTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function single_line(): void
    {
        $this->assertSame(
            '<blockquote>Hello</blockquote>',
            $this->parser->parse('> Hello')->html,
        );
    }

    #[Test]
    public function quote_with_bold(): void
    {
        $this->assertSame(
            '<blockquote>Hello <strong>world</strong></blockquote>',
            $this->parser->parse('> Hello **world**')->html,
        );
    }

    #[Test]
    public function quote_with_italic(): void
    {
        $this->assertSame(
            '<blockquote>Hello <em>world</em></blockquote>',
            $this->parser->parse('> Hello __world__')->html,
        );
    }

    #[Test]
    public function quote_with_link(): void
    {
        $this->assertSame(
            '<blockquote>Hello <a href="#">world</a></blockquote>',
            $this->parser->parse('> Hello [world](#)')->html,
        );
    }

    #[Test]
    public function quote_with_image(): void
    {
        $this->assertSame(
            '<blockquote>Hello <img src="#" alt="world"></blockquote>',
            $this->parser->parse('> Hello ![world](#)')->html,
        );
    }

    #[Test]
    public function nested_levels(): void
    {
        $input = <<<'MD'
        > One
        > > Two
        > > > Three
        > >>> Four
        > > Two again
        MD;

        $expected = <<<'HTML'
        <blockquote>One
        <blockquote>Two
        <blockquote>Three
        <blockquote>Four</blockquote></blockquote>Two again</blockquote></blockquote>
        HTML;

        $this->assertSame($expected, $this->parser->parse($input)->html);
    }
}
