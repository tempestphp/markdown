<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ImageRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class ImageRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new ImageRule()])->parse(
                '![alt](src)',
            );

        $this->assertSame('<img src="src" alt="alt">', $html);
    }

    #[Test]
    public function test_lex_without_alt(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new ImageRule()])->parse(
                '![](src)',
            );

        $this->assertSame('<img src="src">', $html);
    }

    #[Test]
    public function test_invalid_image_throws_exception(): void
    {
        try {
            new Parser(highlighter: null, rules: [new ImageRule()])->parse(
                'Hello ![alt] world',
            );
        } catch (ImageSourceWasMissing $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > Hello ![alt] world
            TXT, $e->getMessage());
        }
    }

    #[Test]
    public function test_invalid_image_source_throws_exception(): void
    {
        try {
            new Parser(highlighter: null, rules: [new ImageRule()])->parse(
                'Hello ![alt](foo world',
            );
        } catch (ImageSourceWasNotClosed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > Hello ![alt](foo world
            TXT, $e->getMessage());
        }
    }

    #[Test]
    public function lex_with_title(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new ImageRule()])->parse(
                '![alt](/a.png "Title")',
            );

        $this->assertSame(
            '<img src="/a.png" alt="alt" title="Title">',
            $html,
        );
    }

    #[Test]
    public function lex_with_angle_bracket_source(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new ImageRule()])->parse(
                '![alt](</my image.png>)',
            );

        $this->assertSame('<img src="/my image.png" alt="alt">', $html);
    }

    #[Test]
    public function lex_with_space_in_source_stays_literal(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new ImageRule(),
            new TextRule(),
        ])->parse(
            'see ![alt](/my image.png) there',
        );

        $this->assertSame('see ![alt](/my image.png) there', $html);
    }
}
