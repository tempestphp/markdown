<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class ParserTest extends TestCase
{
    #[Test]
    public function test_lex_snippet(): void
    {
        $html = (string) new Parser()->parse(<<<'MD'
        # Test
        Hello **world**
        MD);

        $this->assertStringContainsString('<h1 id="test">Test</h1>', $html);
        $this->assertStringContainsString('<p>Hello <strong>world</strong></p>', $html);
    }

    #[Test]
    public function test_lookahead(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        | Test |
        | ---- |
        | Hello |
        MD);

        $result = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

        $this->assertCount(2, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame("| ---- |\n", $result[1]);
        $this->assertSame(0, $parser->position);
    }

    #[Test]
    public function test_lookahead_with_mismatches(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        | Test |

        MD);

        $result = $parser->lookaheadUntil(Parser::NEW_LINE, Parser::NEW_LINE);

        $this->assertCount(1, $result);
        $this->assertSame("| Test |\n", $result[0]);
        $this->assertSame(0, $parser->position);
    }

    #[Test]
    public function test_lookahead_without_match(): void
    {
        $parser = new Parser()->setContent(<<<'MD'
        ABC
        MD);

        $result = $parser->lookaheadUntil('D');

        $this->assertEmpty($result);
    }

    #[Test]
    public function test_comes_next_with_offset(): void
    {
        $parser = new Parser()->setContent('**__');

        $this->assertTrue($parser->comesNext('*'));
        $this->assertTrue($parser->comesNext('*', offset: 1));
        $this->assertFalse($parser->comesNext('*', offset: 2));
        $this->assertTrue($parser->comesNext('_', offset: 2));
        $this->assertFalse($parser->comesNext('_', offset: 10));
    }
}
