<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\Tests\ParserTestCase;

class BoldRuleTest extends ParserTestCase
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
