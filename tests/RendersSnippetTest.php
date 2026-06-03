<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\Parser;

class RendersSnippetTest extends TestCase
{
    #[Test]
    public function test_full_snippet(): void
    {
        try {
            new Parser(highlighter: null, rules: [new ImageRule()])->parse(<<<'MD'
            Foo 1
            Foo 2
            Foo 3
            Hello ![alt] world
            Foo 4
            Foo 5
            Foo 6
            MD);
        } catch (ImageSourceWasMissing $e) {
            $this->assertStringContainsString(<<<'TXT'
            02 | Foo 3
            03 > Hello ![alt] world
            04 | Foo 4
            TXT, $e->getMessage());
        }
    }

    #[Test]
    public function test_top_snippet(): void
    {
        try {
            new Parser(highlighter: null, rules: [new ImageRule()])->parse(<<<'MD'
            Foo 1
            Foo 2
            Foo 3
            Hello ![alt] world
            MD);
        } catch (ImageSourceWasMissing $e) {
            $this->assertStringContainsString(<<<'TXT'
            02 | Foo 3
            03 > Hello ![alt] world
            TXT, $e->getMessage());

            $this->assertStringNotContainsString(<<<'TXT'
            02 | Foo 3
            03 > Hello ![alt] world
            04 | Foo 4
            TXT, $e->getMessage());
        }
    }

    #[Test]
    public function test_bottom_snippet(): void
    {
        try {
            new Parser(highlighter: null, rules: [new ImageRule()])->parse(<<<'MD'
            Hello ![alt] world
            Foo 4
            Foo 5
            Foo 6
            MD);
        } catch (ImageSourceWasMissing $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > Hello ![alt] world
            02 | Foo 4
            03 | Foo 5
            TXT, $e->getMessage());

            $this->assertStringNotContainsString(<<<'TXT'
            01 > Hello ![alt] world
            02 | Foo 4
            03 | Foo 5
            04 | Foo 6
            TXT, $e->getMessage());
        }
    }
}
