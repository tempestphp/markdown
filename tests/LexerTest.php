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

    #[Test]
    public function test_lookahead(): void
    {
        $lexer = new Lexer()->setContent(<<<'MD'
        | Test |
        | ---- |
        | Hello |
        MD);

        $result = $lexer->lookaheadUntil(Lexer::NEW_LINE, Lexer::NEW_LINE);

        $this->assertCount(2, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame("| ---- |\n", $result[1]);
        $this->assertSame(0, $lexer->position);
    }

    #[Test]
    public function test_lookahead_with_mismatches(): void
    {
        $lexer = new Lexer()->setContent(<<<'MD'
        | Test |

        MD);

        $result = $lexer->lookaheadUntil(Lexer::NEW_LINE, Lexer::NEW_LINE);

        $this->assertCount(1, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame(0, $lexer->position);
    }

    #[Test]
    public function test_lookahead_without_match(): void
    {
        $lexer = new Lexer()->setContent(<<<'MD'
        ABC
        MD);

        $result = $lexer->lookaheadUntil('D');

        $this->assertEmpty($result);
    }

    #[Test]
    public function test_comes_next_with_offset(): void
    {
        $lexer = new Lexer()->setContent('**__');

        $this->assertTrue($lexer->comesNext('*'));
        $this->assertTrue($lexer->comesNext('*', offset: 1));
        $this->assertFalse($lexer->comesNext('*', offset: 2));
        $this->assertTrue($lexer->comesNext('_', offset: 2));
        $this->assertFalse($lexer->comesNext('_', offset: 10));
    }

    private function assertTokens(array $expected, TokenCollection $actual): void
    {
        $this->assertCount(count($expected), $actual);

        foreach ($actual as $i => $token) {
            /** @var Token $expected */
            $expectedToken = $expected[$i];
            $actualProperties = (array) $token;
            $expectedProperties = (array) $expectedToken;

            $this->assertSame($token::class, $expectedToken::class);
            $this->assertSame($expectedProperties, $actualProperties);
        }
    }
}
