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
        $html = $this->parser->parse('paragraph with [**bold and __italic__** link](#)');

        $this->assertSame('<p>paragraph with <a href="#"><strong>bold and <em>italic</em></strong> link</a></p>', $html);
    }

    #[Test]
    public function test_with_html_snippets(): void
    {
        $html = $this->parser->parse('paragraph with<br> break');

        $this->assertSame('<p>paragraph with<br> break</p>', $html);

        $html = $this->parser->parse(<<<'MD'
        Hello

        <div>Hello</div>

        <img src="#" />

        <p>
        world
        </p>
        MD);

        $expected = <<<'HTML'
        <p>Hello

        </p><div>Hello</div>

        <img src="#" />

        <p>
        world
        </p>
        HTML;

        $this->assertSame($expected, $html);
    }
}
