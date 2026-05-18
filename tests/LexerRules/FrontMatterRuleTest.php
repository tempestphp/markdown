<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Exceptions\FrontMatterCouldNotBeParsed;
use Tempest\Markdown\Exceptions\FrontMatterShouldBeAnArray;
use Tempest\Markdown\Exceptions\FrontMatterWasNotProperlyClosed;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\FrontMatterRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\Tokens\FrontMatterToken;
use Tempest\Markdown\Tokens\ParagraphToken;

final class FrontMatterRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $tokens = new Lexer([new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->lex(<<<'MD'
        ---
        title: Hello
        foo: bar
        ---

        Bar
        MD);

        $this->assertCount(2, $tokens);
        $this->assertEquals(new FrontMatterToken(['title' => 'Hello', 'foo' => 'bar']), $tokens[0]);
        $this->assertEquals(new ParagraphToken('Bar'), $tokens[1]);
    }

    #[Test]
    public function test_lex_with_longer_frontmatter_lines(): void
    {
        $tokens = new Lexer([new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->lex(<<<'MD'
        -----
        title: Hello
        foo: bar
        -----

        Bar
        MD);

        $this->assertCount(2, $tokens);
        $this->assertEquals(new FrontMatterToken(['title' => 'Hello', 'foo' => 'bar']), $tokens[0]);
        $this->assertEquals(new ParagraphToken('Bar'), $tokens[1]);
    }

    #[Test]
    public function scalar_frontmatter_is_normalized_to_empty_data(): void
    {
        try {
            $tokens = new Lexer([new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->lex(<<<'MD'
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
        $tokens = new Lexer([new FrontMatterRule(), new NewLineRule(), new ParagraphRule()])->lex(<<<'MD'
        ---
        title: Introduction
        description: "Tempest is a framework for PHP development, designed to get out of your way. 
        Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework."
        ---

        Bar
        MD);

        $this->assertCount(2, $tokens);
        $this->assertEquals(
            new FrontMatterToken([
                'title' => 'Introduction',
                'description' => 'Tempest is a framework for PHP development, designed to get out of your way.  Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework.',
            ]),
            $tokens[0],
        );
        $this->assertEquals(new ParagraphToken('Bar'), $tokens[1]);
    }

    #[Test]
    public function invalid_frontmatter_throws_exception(): void
    {
        try {
            new Lexer([new FrontMatterRule()])->lex(<<<'MD'
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
            new Lexer([new FrontMatterRule()])->lex(<<<'MD'
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
