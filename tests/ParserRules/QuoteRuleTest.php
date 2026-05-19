<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\QuoteRule;

class QuoteRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<blockquote>quote</blockquote>', (string) new Parser([new QuoteRule()])->parse('> quote'));
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $this->assertSame(
            '<blockquote>line 1' . "\n" . '<blockquote>line 2</blockquote>line 3</blockquote>',
            (string) new Parser([new QuoteRule()])->parse(<<<'MD'
            > line 1
            > > line 2
            > line 3
            MD),
        );
    }

    #[Test]
    public function test_bold_text(): void
    {
        $this->assertSame('<blockquote>Hello <strong>world</strong></blockquote>', (string) new Parser([new QuoteRule()])->parse('> Hello **world**'));
    }

    #[Test]
    public function test_italic_text(): void
    {
        $this->assertSame('<blockquote>Hello <em>world</em></blockquote>', (string) new Parser([new QuoteRule()])->parse('> Hello __world__'));
    }

    #[Test]
    public function test_link(): void
    {
        $this->assertSame('<blockquote>Hello <a href="#">world</a></blockquote>', (string) new Parser([new QuoteRule()])->parse('> Hello [world](#)'));
    }

    #[Test]
    public function test_image(): void
    {
        $this->assertSame('<blockquote>Hello <img src="#" alt="world"></blockquote>', (string) new Parser([new QuoteRule()])->parse('> Hello ![world](#)'));
    }
}
