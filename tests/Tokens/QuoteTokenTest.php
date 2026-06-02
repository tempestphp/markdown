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
        $token = new QuoteToken('Hello _world_');

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

    #[Test]
    public function test_bold_and_italic_text(): void
    {
        $token = new QuoteToken('Hello ***world***');

        $this->assertEquals('<blockquote>Hello <strong><em>world</em></strong></blockquote>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<blockquote><strong><em>text</em></strong></blockquote>', new QuoteToken('***text***')->parse($parser));
        $this->assertEquals('<blockquote><strong>text</strong></blockquote>', new QuoteToken('**text**')->parse($parser));
        $this->assertEquals('<blockquote><em>text</em></blockquote>', new QuoteToken('*text*')->parse($parser));
        $this->assertEquals('<blockquote><strong><em>text</em></strong></blockquote>', new QuoteToken('___text___')->parse($parser));
        $this->assertEquals('<blockquote><strong>text</strong></blockquote>', new QuoteToken('__text__')->parse($parser));
        $this->assertEquals('<blockquote><em>text</em></blockquote>', new QuoteToken('_text_')->parse($parser));
    }
}
