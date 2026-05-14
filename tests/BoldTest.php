<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class BoldTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function double_asterisk(): void
    {
        $this->assertSame(
            '<p><strong>bold</strong></p>',
            $this->parser->parse('**bold**')->html,
        );
    }

    #[Test]
    public function single_asterisk(): void
    {
        $this->assertSame(
            '<p><strong>bold</strong></p>',
            $this->parser->parse('*bold*')->html,
        );
    }

    #[Test]
    public function bold_containing_italic(): void
    {
        $this->assertSame(
            '<p><strong>hello <em>world</em></strong></p>',
            $this->parser->parse('**hello __world__**')->html,
        );
    }

    #[Test]
    public function bold_containing_strikethrough(): void
    {
        $this->assertSame(
            '<p><strong>hello <s>world</s></strong></p>',
            $this->parser->parse('**hello ~~world~~**')->html,
        );
    }

    #[Test]
    public function bold_containing_link(): void
    {
        $this->assertSame(
            '<p><strong>hello <a href="#">world</a></strong></p>',
            $this->parser->parse('**hello [world](#)**')->html,
        );
    }
}
