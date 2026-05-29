<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Exceptions\ImageSourceWasMissing;
use Tempest\Markdown\Exceptions\ImageSourceWasNotClosed;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ImageRule;
use Tempest\Markdown\Tokens\ImageToken;

class ImageRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ImageRule()])->lex('![alt](src)')[0];

        $this->assertEquals(new ImageToken('src', 'alt'), $token);
    }

    #[Test]
    public function test_lex_without_alt(): void
    {
        $token = new Lexer([new ImageRule()])->lex('![](src)')[0];

        $this->assertEquals(new ImageToken('src', null), $token);
    }

    #[Test]
    public function test_invalid_image_throws_exception(): void
    {
        try {
            new Lexer([new ImageRule()])->lex('Hello ![alt] world');
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
            new Lexer([new ImageRule()])->lex('Hello ![alt](foo world');
        } catch (ImageSourceWasNotClosed $e) {
            $this->assertStringContainsString(<<<'TXT'
            01 > Hello ![alt](foo world
            TXT, $e->getMessage());
        }
    }
}
