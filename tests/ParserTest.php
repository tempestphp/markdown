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
        $parsed = $this->parser->parse('paragraph with [**bold and __italic__** link](#)');

        $this->assertSame('<p>paragraph with <a href="#"><strong>bold and <em>italic</em></strong> link</a></p>', $parsed->html);
    }

    #[Test]
    public function test_with_html_snippets(): void
    {
        $parsed = $this->parser->parse('paragraph with<br> break');

        $this->assertSame('<p>paragraph with<br> break</p>', $parsed->html);

        $parsed = $this->parser->parse(<<<'MD'
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

        $this->assertSame($expected, $parsed->html);
    }

    #[Test]
    public function test_with_front_matter(): void
    {
        $parsed = $this->parser->parse(<<<'MD'
        ---
        title: Hello
        foo: bar
        ---

        Hello
        MD);

        $this->assertSame('<p>Hello</p>', (string) $parsed);
        $this->assertSame(
            [
                'title' => 'Hello',
                'foo' => 'bar',
            ],
            $parsed->frontMatter,
        );
    }

    #[Test]
    public function test_div_without_class(): void
    {
        $parsed = $this->parser->parse(":::\nHello\n:::");

        $this->assertSame('<div>Hello\n</div>', $parsed->html);
    }

    #[Test]
    public function test_div_with_class(): void
    {
        $parsed = $this->parser->parse(":::warning\nHello\n:::");

        $this->assertSame('<div class="warning">Hello\n</div>', $parsed->html);
    }
}
