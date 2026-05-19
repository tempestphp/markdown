<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\QuoteRule;

class QuoteRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<blockquote>quote</blockquote>', (string) new Parser([new QuoteRule()])->parse('> quote'));
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $this->assertSame(
            '<blockquote>line 1' . "\n" . '<blockquote>line 2</blockquote>line 3</blockquote>',
            (string) new Parser([new QuoteRule()])->parse(<<<'MD'
            > line 1
            > > line 2
            > line 3
            MD),
        );
    }
}
