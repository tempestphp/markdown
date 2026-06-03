<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\Parser;

class QuoteRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new QuoteRule()])->parse('> quote');

        $this->assertSame('<blockquote>quote</blockquote>', $html);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new QuoteRule()])->parse(<<<'MD'
        > line 1
        > > line 2
        > line 3
        MD);

        $this->assertSame("<blockquote>line 1\n<blockquote>line 2</blockquote>line 3</blockquote>", $html);
    }

    #[Test]
    public function test_lex_only_at_start_of_line(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new QuoteRule()])->parse('two > one');

        $this->assertSame('', $html);
    }
}
