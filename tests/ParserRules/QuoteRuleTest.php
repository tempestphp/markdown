<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\QuoteRule;
use Tempest\Markdown\Tests\ParserTestCase;

class QuoteRuleTest extends ParserTestCase
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
