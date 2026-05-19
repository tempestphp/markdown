<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ImageRule;

class ImageRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<img src="href" alt="alt">', (string) new Parser([new ImageRule()])->parse('![alt](href)'));
    }

    #[Test]
    public function test_lex_without_alt(): void
    {
        $this->assertSame('<img src="href">', (string) new Parser([new ImageRule()])->parse('![](href)'));
    }

    #[Test]
    public function test_invalid_image_throws_exception(): void
    {
        try {
            new Parser([new ImageRule()])->parse('Hello ![alt] world');
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
            new Parser([new ImageRule()])->parse('Hello ![alt](foo world');
        } catch (ImageSourceWasNotClosed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > Hello ![alt](foo world
            TXT, $e->getMessage());
        }
    }
}
