<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\Token;
use Tempest\Markdown\TokenCollection;
use Tempest\Markdown\Tokens\HeadingToken;
use Tempest\Markdown\Tokens\NewLineToken;
use Tempest\Markdown\Tokens\ParagraphToken;

final class LexerTest extends TestCase
{
    private Lexer $lexer;

    #[Before]
    public function setupParser(): void
    {
        $this->lexer = new Lexer();
    }

    #[Test]
    public function test_lex_snippet(): void
    {
        $tokens = $this->lexer->lex(<<<'MD'
        # Test
        Hello **world**
        MD);

        $this->assertTokens(
            expected: [
                new HeadingToken('Test', 1),
                new NewLineToken("\n"),
                new ParagraphToken('Hello **world**'),
            ],
            actual: $tokens,
        );
    }

    private function assertTokens(array $expected, TokenCollection $actual): void
    {
        $this->assertCount(count($expected), $actual);

        foreach ($actual as $i => $token) {
            /** @var Token $expected */
            $expectedToken = $expected[$i]; // @mago-ignore all
            $actualProperties = (array) $token; // @mago-ignore all
            $expectedProperties = (array) $expectedToken; // @mago-ignore all

            $this->assertSame($token::class, $expectedToken::class); // @mago-ignore all
            $this->assertSame($expectedProperties, $actualProperties); // @mago-ignore all
        }
    }
}
