<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ItalicTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function double_underscore(): void
    {
        $this->assertSame(
            '<p><em>italic</em></p>',
            $this->parser->parse('__italic__')->html,
        );
    }

    #[Test]
    public function single_underscore(): void
    {
        $this->assertSame(
            '<p><em>italic</em></p>',
            $this->parser->parse('_italic_')->html,
        );
    }

    #[Test]
    public function italic_containing_bold(): void
    {
        $this->assertSame(
            '<p><em>hello <strong>world</strong></em></p>',
            $this->parser->parse('__hello **world**__')->html,
        );
    }

    #[Test]
    public function italic_containing_strikethrough(): void
    {
        $this->assertSame(
            '<p><em>hello <s>world</s></em></p>',
            $this->parser->parse('__hello ~~world~~__')->html,
        );
    }

    #[Test]
    public function italic_containing_link(): void
    {
        $this->assertSame(
            '<p><em>hello <a href="#">world</a></em></p>',
            $this->parser->parse('__hello [world](#)__')->html,
        );
    }
}
