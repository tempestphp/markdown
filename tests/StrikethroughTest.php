<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

/**
 * Strikethrough is not a top-level paragraph inline rule. It activates only
 * inside bold (`*..*`), italic (`_.._`), link (`[..]`), or strike contexts.
 * Tests exercise it through those entry points.
 */
final class StrikethroughTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function strike_inside_bold_double_tilde(): void
    {
        $this->assertSame(
            '<p><strong><s>deleted</s></strong></p>',
            $this->parser->parse('**~~deleted~~**')->html,
        );
    }

    #[Test]
    public function strike_inside_bold_single_tilde(): void
    {
        $this->assertSame(
            '<p><strong><s>deleted</s></strong></p>',
            $this->parser->parse('**~deleted~**')->html,
        );
    }

    #[Test]
    public function strike_inside_italic(): void
    {
        $this->assertSame(
            '<p><em><s>deleted</s></em></p>',
            $this->parser->parse('__~~deleted~~__')->html,
        );
    }

    #[Test]
    public function strike_inside_link(): void
    {
        $this->assertSame(
            '<p><a href="#"><s>deleted</s></a></p>',
            $this->parser->parse('[~~deleted~~](#)')->html,
        );
    }

    #[Test]
    public function strike_containing_italic(): void
    {
        $this->assertSame(
            '<p><strong><s>hello <em>world</em></s></strong></p>',
            $this->parser->parse('**~~hello __world__~~**')->html,
        );
    }

    #[Test]
    public function strike_containing_link(): void
    {
        $this->assertSame(
            '<p><strong><s>hello <a href="#">world</a></s></strong></p>',
            $this->parser->parse('**~~hello [world](#)~~**')->html,
        );
    }

    #[Test]
    public function tildes_in_plain_paragraph_pass_through(): void
    {
        // No strike at top-level paragraph: tildes render as literal text.
        $this->assertSame(
            '<p>~~plain~~</p>',
            $this->parser->parse('~~plain~~')->html,
        );
    }
}
