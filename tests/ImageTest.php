<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ImageTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function image_with_alt(): void
    {
        $this->assertSame(
            '<p><img src="https://example.com/img.png" alt="a cat"></p>',
            $this->parser->parse('![a cat](https://example.com/img.png)')->html,
        );
    }

    #[Test]
    public function image_without_alt(): void
    {
        $this->assertSame(
            '<p><img src="https://example.com/img.png"></p>',
            $this->parser->parse('![](https://example.com/img.png)')->html,
        );
    }

    #[Test]
    public function bare_exclamation_passes_through(): void
    {
        // A `!` not followed by `[` is not an image trigger and must render as text.
        $this->assertSame(
            '<p>hello!</p>',
            $this->parser->parse('hello!')->html,
        );
    }
}
