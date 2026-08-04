<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HeadingRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_h1(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('# Hello');

        $this->assertSame('<h1 id="hello">Hello</h1>', $html);
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('### Hello');

        $this->assertSame('<h3 id="hello">Hello</h3>', $html);
    }

    #[Test]
    public function test_lex_with_heading_id(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('### Hello ### hello-world');

        $this->assertSame('<h3 id="hello-world">Hello</h3>', $html);
    }

    #[Test]
    public function test_slug_is_constrained_to_a_safe_alphabet(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('# Hello, "World" & Friends!');

        $this->assertSame('<h1 id="hello-world-friends">Hello, "World" & Friends!</h1>', $html);
    }

    #[Test]
    public function test_slug_cannot_break_out_of_the_id_attribute(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('# h " onclick="alert(1)');

        $this->assertSame('<h1 id="h-onclick-alert-1">h " onclick="alert(1)</h1>', $html);
    }
}
