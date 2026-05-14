<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class RulerTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function thin_ruler_three_dashes(): void
    {
        // `---` at byte 0 is front matter; we put a paragraph above so it's a ruler.
        $this->assertSame(
            "<p>x\n</p><hr/>",
            $this->parser->parse("x\n---")->html,
        );
    }

    #[Test]
    public function thin_ruler_long(): void
    {
        $this->assertSame(
            "<p>x\n</p><hr/>",
            $this->parser->parse("x\n-----")->html,
        );
    }

    #[Test]
    public function thick_ruler_three_equals(): void
    {
        $this->assertSame(
            '<hr/>',
            $this->parser->parse('===')->html,
        );
    }

    #[Test]
    public function thick_ruler_long(): void
    {
        $this->assertSame(
            '<hr/>',
            $this->parser->parse('=====')->html,
        );
    }
}
