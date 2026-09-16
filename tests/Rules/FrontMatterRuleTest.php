<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Exceptions\FrontMatterCouldNotBeParsed;
use Tempest\Markdown\Exceptions\FrontMatterShouldBeAnArray;
use Tempest\Markdown\Exceptions\FrontMatterWasNotProperlyClosed;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\FrontMatterRule;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Rules\ThinRulerRule;
use Tempest\Markdown\Tests\ParserTestCase;

final class FrontMatterRuleTest extends ParserTestCase
{
    #[Test]
    public function standalone_delimiter_is_a_thematic_break(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new FrontMatterRule(),
            new ThinRulerRule(),
            new NewLineRule(),
            new TextRule(),
        ]);

        $this->assertSame('<hr/>', $parser->parse('---')->html);
        $this->assertSame("<hr/>\n", $parser->parse("---\n")->html);
        $this->assertSame('<hr/>', $parser->parse('-----')->html);
        $this->assertSame(
            '<hr/>',
            new Markdown(highlighter: null)->parse('---')->html,
        );
        $this->assertSame(
            '<hr/>',
            new Markdown(highlighter: null)->parse("--- \t")->html,
        );
        $this->assertSame(
            "<hr/>\n",
            new Markdown(highlighter: null)->parse("--- \t\n")->html,
        );
    }

    #[Test]
    public function unclosed_frontmatter_with_content_still_throws(): void
    {
        $this->expectException(FrontMatterWasNotProperlyClosed::class);

        new Markdown(highlighter: null)->parse("---\ntitle: Hello");
    }

    #[Test]
    public function test_lex(): void
    {
        $parsed = new Parser(highlighter: null, rules: [
            new FrontMatterRule(),
            new NewLineRule(),
            new ParagraphRule(),
            new TextRule(),
        ])->parse(<<<'MD'
        ---
        title: Hello
        foo: bar
        ---

        Bar
        MD);

        $this->assertSame(
            ['title' => 'Hello', 'foo' => 'bar'],
            $parsed->frontmatter,
        );
        $this->assertSame('<p>Bar</p>', $parsed->html);
    }

    #[Test]
    public function test_lex_with_longer_frontmatter_lines(): void
    {
        $parsed = new Parser(highlighter: null, rules: [
            new FrontMatterRule(),
            new NewLineRule(),
            new ParagraphRule(),
            new TextRule(),
        ])->parse(<<<'MD'
        -----
        title: Hello
        foo: bar
        -----

        Bar
        MD);

        $this->assertSame(
            ['title' => 'Hello', 'foo' => 'bar'],
            $parsed->frontmatter,
        );
        $this->assertSame('<p>Bar</p>', $parsed->html);
    }

    #[Test]
    public function scalar_frontmatter_is_normalized_to_empty_data(): void
    {
        try {
            new Parser(highlighter: null, rules: [
                new FrontMatterRule(),
                new NewLineRule(),
                new ParagraphRule(),
                new TextRule(),
            ])->parse(<<<'MD'
            ---
            just text
            ---

            Body
            MD);
        } catch (FrontMatterShouldBeAnArray $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > ---
            02 | just text
            03 | ---
            TXT, $e->getMessage());
        }
    }

    #[Test]
    public function test_complex_frontmatter(): void
    {
        $parsed = new Parser(highlighter: null, rules: [
            new FrontMatterRule(),
            new NewLineRule(),
            new ParagraphRule(),
            new TextRule(),
        ])->parse(<<<'MD'
        ---
        title: Introduction
        description: "Tempest is a framework for PHP development, designed to get out of your way.
        Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework."
        ---

        Bar
        MD);

        $this->assertSame(
            [
                'title' => 'Introduction',
                'description' => 'Tempest is a framework for PHP development, designed to get out of your way. Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework.',
            ],
            $parsed->frontmatter,
        );
        $this->assertSame('<p>Bar</p>', $parsed->html);
    }

    #[Test]
    public function invalid_frontmatter_throws_exception(): void
    {
        try {
            new Parser(highlighter: null, rules: [
                new FrontMatterRule(),
                new TextRule(),
            ])->parse(
                <<<'MD'
                ---
                title: "Introduction
                ---
                MD,
            );
        } catch (FrontMatterCouldNotBeParsed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > ---
            02 | title: "Introduction
            03 | ---
            TXT, $e->getMessage());
        }
    }

    #[Test]
    public function unclosed_frontmatter_throws_exception(): void
    {
        try {
            new Parser(highlighter: null, rules: [
                new FrontMatterRule(),
                new TextRule(),
            ])->parse(
                <<<'MD'
                ---
                title: "Introduction"

                Paragraph
                MD,
            );
        } catch (FrontMatterWasNotProperlyClosed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > ---
            02 | title: "Introduction"
            03 |
            TXT, $e->getMessage());
        }
    }
}
