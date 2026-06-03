<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Exceptions\FrontMatterCouldNotBeParsed;
use Tempest\Markdown\Exceptions\FrontMatterShouldBeAnArray;
use Tempest\Markdown\Exceptions\FrontMatterWasNotProperlyClosed;
use Tempest\Markdown\LexerRules\FrontMatterRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\Parser;

final class FrontMatterRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $parsed = new Parser(highlighter: null, rules: [new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->parse(<<<'MD'
        ---
        title: Hello
        foo: bar
        ---

        Bar
        MD);

        $this->assertSame(['title' => 'Hello', 'foo' => 'bar'], $parsed->frontmatter);
        $this->assertSame('<p>Bar</p>', $parsed->html);
    }

    #[Test]
    public function test_lex_with_longer_frontmatter_lines(): void
    {
        $parsed = new Parser(highlighter: null, rules: [new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->parse(<<<'MD'
        -----
        title: Hello
        foo: bar
        -----

        Bar
        MD);

        $this->assertSame(['title' => 'Hello', 'foo' => 'bar'], $parsed->frontmatter);
        $this->assertSame('<p>Bar</p>', $parsed->html);
    }

    #[Test]
    public function scalar_frontmatter_is_normalized_to_empty_data(): void
    {
        try {
            new Parser(highlighter: null, rules: [new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->parse(<<<'MD'
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
        $parsed = new Parser(highlighter: null, rules: [new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->parse(<<<'MD'
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
            new Parser(highlighter: null, rules: [new FrontMatterRule()])->parse(<<<'MD'
            ---
            title: "Introduction
            ---
            MD);
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
            new Parser(highlighter: null, rules: [new FrontMatterRule()])->parse(<<<'MD'
            ---
            title: "Introduction"

            Paragraph
            MD);
        } catch (FrontMatterWasNotProperlyClosed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > ---
            02 | title: "Introduction"
            03 |
            TXT, $e->getMessage());
        }
    }
}
