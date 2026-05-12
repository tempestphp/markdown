<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\CodeRule;
use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\LexerRules\LinkRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\PreRule;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\LexerRules\ThickRulerRule;
use Tempest\Markdown\LexerRules\ThinRulerRule;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\TokenCollection;
use Tempest\Markdown\Tokens\BoldToken;
use Tempest\Markdown\Tokens\CodeToken;
use Tempest\Markdown\Tokens\HeadingToken;
use Tempest\Markdown\Tokens\ImageToken;
use Tempest\Markdown\Tokens\ItalicToken;
use Tempest\Markdown\Tokens\LinkToken;
use Tempest\Markdown\Tokens\NewLineToken;
use Tempest\Markdown\Tokens\PreToken;
use Tempest\Markdown\Tokens\QuoteToken;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;
use Tempest\Markdown\Tokens\TextToken;

final class LexerTest extends TestCase
{
    private Lexer $lexer;

    #[Before]
    public function setupParser(): void
    {
        $this->lexer = new Lexer([
            new NewLineRule(),
            new HeadingRule(),
            new BoldRule(),
            new ItalicRule(),
            new QuoteRule(),
            new PreRule(),
            new CodeRule(),
            new LinkRule(),
            new ImageRule(),
            new ThinRulerRule(),
            new ThickRulerRule(),
            new ParagraphRule(),
        ]);
    }

    #[Test]
    public function test_lex_heading(): void
    {
        $tokens = $this->lexer->lex('# Test');

        $this->assertTokens(
            expected: [
                new HeadingToken('Test', 1),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $tokens = $this->lexer->lex('#### Test');

        $this->assertTokens(
            expected: [
                new HeadingToken('Test', 4),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_lex_multiple_headings(): void
    {
        $tokens = $this->lexer->lex(<<<'MD'
        # Test
        ## Test 2
        ### Test 3
        MD);

        $this->assertTokens(
            expected: [
                new HeadingToken('Test', 1),
                new NewLineToken("\n"),
                new HeadingToken('Test 2', 2),
                new NewLineToken("\n"),
                new HeadingToken('Test 3', 3),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_bold(): void
    {
        $tokens = $this->lexer->lex('**bold**');

        $this->assertTokens(
            expected: [
                new BoldToken('bold'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_bold_single_character(): void
    {
        $tokens = $this->lexer->lex('*bold*');

        $this->assertTokens(
            expected: [
                new BoldToken('bold'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_italic(): void
    {
        $tokens = $this->lexer->lex('__italic__');

        $this->assertTokens(
            expected: [
                new ItalicToken('italic'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_italic_single_character(): void
    {
        $tokens = $this->lexer->lex('_italic_');

        $this->assertTokens(
            expected: [
                new ItalicToken('italic'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_quote(): void
    {
        $tokens = $this->lexer->lex('> quote');

        $this->assertTokens(
            expected: [
                new QuoteToken('quote', 1),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_deep_quote(): void
    {
        $tokens = $this->lexer->lex('>>>> quote');

        $this->assertTokens(
            expected: [
                new QuoteToken('quote', 4),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_pre_with_language(): void
    {
        $tokens = $this->lexer->lex(<<<'MD'
        ```md
        # Code
        
        ## Test
        ```
        MD);

        $this->assertTokens(
            expected: [
                new PreToken(
                    language: 'md',
                    content: "# Code\n\n## Test",
                ),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_pre_without_language(): void
    {
        $tokens = $this->lexer->lex(<<<'MD'
        ```
        # Code
        
        ## Test
        ```
        MD);

        $this->assertTokens(
            expected: [
                new PreToken(
                    language: null,
                    content: "# Code\n\n## Test",
                ),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_code(): void
    {
        $tokens = $this->lexer->lex('`code`');

        $this->assertTokens(
            expected: [
                new CodeToken('code'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_code_with_whitespace(): void
    {
        $tokens = $this->lexer->lex('` code `');

        $this->assertTokens(
            expected: [
                new CodeToken(' code '),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_link(): void
    {
        $tokens = $this->lexer->lex('[a](href)');

        $this->assertTokens(
            expected: [
                new LinkToken('a', 'href'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_link_with_hashtag(): void
    {
        $tokens = $this->lexer->lex('[a](#href)');

        $this->assertTokens(
            expected: [
                new LinkToken('a', '#href'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_link_without_hashtag(): void
    {
        $tokens = $this->lexer->lex('[a]');

        $this->assertTokens(
            expected: [
                new LinkToken('a', null),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_thin_ruler(): void
    {
        $tokens = $this->lexer->lex('---');

        $this->assertTokens(
            expected: [
                new RulerToken('---', RulerType::THIN),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_long_thin_ruler(): void
    {
        $tokens = $this->lexer->lex('-----');

        $this->assertTokens(
            expected: [
                new RulerToken('-----', RulerType::THIN),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_thick_ruler(): void
    {
        $tokens = $this->lexer->lex('===');

        $this->assertTokens(
            expected: [
                new RulerToken('===', RulerType::THICK),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_long_thick_ruler(): void
    {
        $tokens = $this->lexer->lex('=====');

        $this->assertTokens(
            expected: [
                new RulerToken('=====', RulerType::THICK),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_image(): void
    {
        $tokens = $this->lexer->lex('![alt](href)');

        $this->assertTokens(
            expected: [
                new ImageToken('href', 'alt'),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_image_without_alt(): void
    {
        $tokens = $this->lexer->lex('![](href)');

        $this->assertTokens(
            expected: [
                new ImageToken('href', null),
            ],
            actual: $tokens,
        );
    }

    #[Test]
    public function test_text(): void
    {
        $tokens = new Lexer([
            new BoldRule(),
            new TextRule(),
        ])->lex('Hello **world**!');

        $this->assertTokens(
            expected: [
                new TextToken('Hello '),
                new BoldToken('world'),
                new TextToken('!'),
            ],
            actual: $tokens,
        );
    }

    // TOOD: Lists
    // TODO: nested elements

    private function assertTokens(array $expected, TokenCollection $actual): void
    {
        $this->assertCount(count($expected), $actual);

        foreach ($actual as $i => $token) {
            $actualProperties = (array) $token;
            $expectedProperties = (array) $expected[$i];

            $this->assertSame($token::class, $expected[$i]::class);
            $this->assertSame($expectedProperties, $actualProperties);
        }
    }
}
