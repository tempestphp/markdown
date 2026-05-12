<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ParserTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser();
    }

    #[Test]
    public function test_token_with_nested_tokens(): void
    {
        $html = $this->parser->parse('paragraph with **bold** text');

        $this->assertSame('<p>paragraph with <strong>bold</strong> text</p>', $html);
    }
}