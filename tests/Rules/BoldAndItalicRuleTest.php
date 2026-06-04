<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldAndItalicRule;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Tests\ParserTestCase;

class BoldAndItalicRuleTest extends ParserTestCase
{
    #[Test]
    public function test_triple_asterisk_bold_and_italic(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldAndItalicRule(), new BoldRule(), new ItalicRule()])->parse('***text***');

        $this->assertSame('<strong><em>text</em></strong>', $html);
    }

    #[Test]
    public function test_triple_underscore_bold_and_italic(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldAndItalicRule(), new BoldRule(), new ItalicRule()])->parse('___text___');

        $this->assertSame('<strong><em>text</em></strong>', $html);
    }

    #[Test]
    public function test_does_not_lex_double_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldAndItalicRule()])->parse('**text**');

        $this->assertSame('', $html);
    }

    #[Test]
    public function test_does_not_lex_single_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldAndItalicRule()])->parse('*text*');

        $this->assertSame('', $html);
    }

    #[Test]
    public function test_does_not_lex_double_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldAndItalicRule()])->parse('__text__');

        $this->assertSame('', $html);
    }
}
