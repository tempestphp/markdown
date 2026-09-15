<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HeadingRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_h1(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '# Hello',
        );

        $this->assertSame('<h1 id="hello">Hello</h1>', $html);
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '### Hello',
        );

        $this->assertSame('<h3 id="hello">Hello</h3>', $html);
    }

    #[Test]
    public function test_heading_text_shorter_than_level(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '#### Fin',
        );

        $this->assertSame('<h4 id="fin">Fin</h4>', $html);
    }

    #[Test]
    public function test_lex_with_heading_id(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '### Hello ### hello-world',
        );

        $this->assertSame('<h3 id="hello-world">Hello</h3>', $html);
    }

    #[Test]
    public function test_slug_is_constrained_to_a_safe_alphabet(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '# Hello, "World" & Friends!',
        );

        $this->assertSame(
            '<h1 id="hello-world-friends">Hello, "World" & Friends!</h1>',
            $html,
        );
    }

    #[Test]
    public function test_slug_cannot_break_out_of_the_id_attribute(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '# h " onclick="alert(1)',
        );

        $this->assertSame(
            '<h1 id="h-onclick-alert-1">h " onclick="alert(1)</h1>',
            $html,
        );
    }

    #[Test]
    public function lex_generates_an_id_by_default(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new TextRule(),
        ])->parse(
            '## A heading',
        );

        $this->assertSame('<h2 id="a-heading">A heading</h2>', $html);
    }

    #[Test]
    public function lex_without_generated_ids(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(generateIds: false),
            new TextRule(),
        ])->parse('## A heading');

        $this->assertSame('<h2>A heading</h2>', $html);
    }

    #[Test]
    public function lex_keeps_an_explicit_id_without_generated_ids(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(generateIds: false),
            new TextRule(),
        ])->parse('## A heading ## custom-id');

        $this->assertSame('<h2 id="custom-id">A heading</h2>', $html);
    }
}
