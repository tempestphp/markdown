<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ParserTest extends TestCase
{
    private Parser $parser;

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

        $this->assertSame("<div>Hello\n</div>", $parsed->html);
    }

    #[Test]
    public function test_div_with_class(): void
    {
        $parsed = $this->parser->parse(":::warning\nHello\n:::");

        $this->assertSame("<div class=\"warning\">Hello\n</div>", $parsed->html);
    }

    #[Test]
    public function test_table(): void
    {
        $parsed = $this->parser->parse(<<<MD
        | Name | Age |
        | --- | --- |
        | Alice | 30 |
        | Bob | 25 |
        MD);

        $this->assertSame(
            '<table><thead><tr><th>Name</th><th>Age</th></tr></thead><tbody><tr><td>Alice</td><td>30</td></tr><tr><td>Bob</td><td>25</td></tr></tbody></table>',
            $parsed->html,
        );
    }

    #[Test]
    public function test_list(): void
    {
        $parsed = $this->parser->parse(<<<'MD'
        - a
        - b
        MD);

        $this->assertSame('<ul><li>a</li><li>b</li></ul>', $parsed->html);
    }

    #[Test]
    public function test_ordered_list(): void
    {
        $parsed = $this->parser->parse(<<<'MD'
        1. a
        2. b
        MD);

        $this->assertSame('<ol><li>a</li><li>b</li></ol>', $parsed->html);
    }

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser();
    }

    #[Test]
    public function test_lex_snippet(): void
    {
        $result = $this->parser->parse(<<<'MD'
        # Test
        Hello **world**
        MD);

        $this->assertSame('<h1 id="test">Test</h1>' . "\n" . '<p>Hello <strong>world</strong></p>', $result->html);
    }

    #[Test]
    public function test_lookahead(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        | Test |
        | ---- |
        | Hello |
        MD);

        $result = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

        $this->assertCount(2, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame("| ---- |\n", $result[1]);
        $this->assertSame(0, $parser->position);
    }

    #[Test]
    public function test_lookahead_with_mismatches(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        | Test |

        MD);

        $result = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

        $this->assertCount(1, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame(0, $parser->position);
    }

    #[Test]
    public function test_lookahead_without_match(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        ABC
        MD);

        $result = $parser->lookaheadUntil('D');

        $this->assertEmpty($result);
    }
}
