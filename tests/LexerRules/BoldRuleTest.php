<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\Parser;

class BoldRuleTest extends TestCase
{
    #[Test]
    public function test_lex_double_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('**bold**');

        $this->assertSame('<strong>bold</strong>', $html);
    }

    #[Test]
    public function test_lex_asterisk_with_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('**_bold_**');

        $this->assertSame('<strong><em>bold</em></strong>', $html);
    }

    #[Test]
    public function test_does_not_lex_single_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('*bold*');

        $this->assertSame('', $html);
    }

    #[Test]
    public function test_lex_double_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('__bold__');

        $this->assertSame('<strong>bold</strong>', $html);
    }

    #[Test]
    public function test_does_not_lex_single_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('_bold_');

        $this->assertSame('', $html);
    }

    #[Test]
    public function test_underscore_with_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule()])->parse('__*bold*__');

        $this->assertSame('<strong><em>bold</em></strong>', $html);
    }
}
