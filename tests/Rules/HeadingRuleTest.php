<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HeadingRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_h1(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
                '# Hello',
            );

        $this->assertSame('<h1 id="hello">Hello</h1>', $html);
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
                '### Hello',
            );

        $this->assertSame('<h3 id="hello">Hello</h3>', $html);
    }

    #[Test]
    public function test_heading_text_shorter_than_level(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
                '#### Fin',
            );

        $this->assertSame('<h4 id="fin">Fin</h4>', $html);
    }

    #[Test]
    public function test_lex_with_heading_id(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
                '### Hello ### hello-world',
            );

        $this->assertSame('<h3 id="hello-world">Hello</h3>', $html);
    }

    #[Test]
    public function test_slug_is_constrained_to_a_safe_alphabet(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
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
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
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
        $html =
            (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse(
                '## A heading',
            );

        $this->assertSame('<h2 id="a-heading">A heading</h2>', $html);
    }

    #[Test]
    public function lex_without_generated_ids(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(generateIds: false),
        ])->parse('## A heading');

        $this->assertSame('<h2>A heading</h2>', $html);
    }

    #[Test]
    public function lex_keeps_an_explicit_id_without_generated_ids(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(generateIds: false),
        ])->parse('## A heading ## custom-id');

        $this->assertSame('<h2 id="custom-id">A heading</h2>', $html);
    }

    #[Test]
    public function lex_without_a_space_after_the_marker_is_not_a_heading(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new ParagraphRule(),
        ]);

        $this->assertSame('<p>#titre</p>', (string) $parser->parse('#titre'));
        $this->assertSame(
            '<p>#hashtag</p>',
            (string) $parser->parse('#hashtag'),
        );
        $this->assertSame('<p>#5 bolt</p>', (string) $parser->parse('#5 bolt'));
    }

    #[Test]
    public function lex_more_than_six_markers_is_not_a_heading(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new HeadingRule(),
            new ParagraphRule(),
        ])->parse('####### seven');

        $this->assertSame('<p>####### seven</p>', $html);
    }

    #[Test]
    public function lex_marker_alone_is_an_empty_heading(): void
    {
        $parser = new Parser(highlighter: null, rules: [new HeadingRule()]);

        $this->assertSame('<h1></h1>', (string) $parser->parse('#'));
        $this->assertSame('<h2></h2>', (string) $parser->parse('##'));
    }
}
