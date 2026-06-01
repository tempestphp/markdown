<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\QuoteToken;

class QuoteTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new QuoteToken('Hello');

        $this->assertEquals('<blockquote>Hello</blockquote>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_multiple_levels(): void
    {
        $token = new QuoteToken(<<<'TXT'
        One
        > Two
        > > Three
        >>> Four
        > Two again
        TXT);

        $parsed = $token->parse(new Parser());

        $expected = <<<'HTML'
        <blockquote>One
        <blockquote>Two
        <blockquote>Three
        <blockquote>Four</blockquote></blockquote>Two again</blockquote></blockquote>
        HTML;

        $this->assertEquals($expected, $parsed);
    }

    #[Test]
    public function test_parse_multiple_levels_only_at_start_of_line(): void
    {
        $token = new QuoteToken(<<<'TXT'
        two > one
        TXT);

        $parsed = $token->parse(new Parser());

        $this->assertSame('<blockquote>two > one</blockquote>', $parsed);
    }

    #[Test]
    public function test_bold_text(): void
    {
        $token = new QuoteToken('Hello **world**');

        $this->assertEquals('<blockquote>Hello <strong>world</strong></blockquote>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_italic_text(): void
    {
        $token = new QuoteToken('Hello __world__');

        $this->assertEquals('<blockquote>Hello <em>world</em></blockquote>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_link(): void
    {
        $token = new QuoteToken('Hello [world](#)');

        $this->assertEquals('<blockquote>Hello <a href="#">world</a></blockquote>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_image(): void
    {
        $token = new QuoteToken('Hello ![world](#)');

        $this->assertEquals('<blockquote>Hello <img src="#" alt="world"></blockquote>', $token->parse(new Parser()));
    }
}
