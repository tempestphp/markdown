<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\Parser;

class ImageRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ImageRule()])->parse('![alt](src)');

        $this->assertSame('<img src="src" alt="alt">', $html);
    }

    #[Test]
    public function test_lex_without_alt(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ImageRule()])->parse('![](src)');

        $this->assertSame('<img src="src">', $html);
    }

    #[Test]
    public function test_invalid_image_throws_exception(): void
    {
        try {
            new Parser(highlighter: null, rules: [new ImageRule()])->parse('Hello ![alt] world');
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
            new Parser(highlighter: null, rules: [new ImageRule()])->parse('Hello ![alt](foo world');
        } catch (ImageSourceWasNotClosed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > Hello ![alt](foo world
            TXT, $e->getMessage());
        }
    }
}
