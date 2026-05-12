<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\MarkdownParser;

final class MarkdownParserTest extends TestCase
{
    private MarkdownParser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new MarkdownParser();
    }

    #[Test]
    public function test_paragraph(): void
    {
        $html = $this->parser->parse('paragraph');

        $this->assertSame('<p>paragraph</p>', $html);
    }

    #[Test]
    public function test_paragraph_with_bold_text(): void
    {
        $html = $this->parser->parse('paragraph with **bold** text');

        $this->assertSame('<p>paragraph with <strong>bold</strong> text</p>', $html);
    }
}