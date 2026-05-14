<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class HtmlTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function html_at_block_start_passes_through(): void
    {
        $this->assertSame(
            '<p>Hi</p>',
            $this->parser->parse('<p>Hi</p>')->html,
        );
    }

    #[Test]
    public function nested_same_name_tags_are_balanced(): void
    {
        $this->assertSame(
            '<div><div>Hi</div></div>',
            $this->parser->parse('<div><div>Hi</div></div>')->html,
        );
    }

    #[Test]
    public function html_inside_paragraph_is_inlined(): void
    {
        // A `<br>`-style tag appearing mid-paragraph is consumed as paragraph content.
        $this->assertSame(
            '<p>paragraph with<br> break</p>',
            $this->parser->parse('paragraph with<br> break')->html,
        );
    }

    #[Test]
    public function html_block_after_paragraph(): void
    {
        $input = <<<'MD'
        Hello

        <p>
        Hi
        </p>

        World
        MD;

        $this->assertStringContainsString('<p>Hello', $this->parser->parse($input)->html);
        $this->assertStringContainsString("<p>\nHi\n</p>", $this->parser->parse($input)->html);
    }
}
