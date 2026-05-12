<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Markdown;

final class MarkdownTest extends TestCase
{
    private Markdown $markdown;

    #[Before]
    public function setupMarkdown(): void
    {
        $this->markdown = new Markdown();
    }

    #[Test]
    public function test_parse(): void
    {
        $parsed = $this->markdown->parse('**Hello**');

        $this->assertSame('<p><strong>Hello</strong></p>', $parsed);
    }
}
