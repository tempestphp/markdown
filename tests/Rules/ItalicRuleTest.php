<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ItalicRule;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Tests\ParserTestCase;

class ItalicRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_with_underscore(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ItalicRule()])->parse('_italic_');

        $this->assertSame('<em>italic</em>', $html);
    }

    #[Test]
    public function test_underscore_must_be_terminated(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule(), new ItalicRule(), new ParagraphRule()])->parse("Hello_world\n\nHi");

        $this->assertSame("<p>Hello_world</p>\n\n<p>Hi</p>", $html);
    }

    #[Test]
    public function test_lex_with_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ItalicRule()])->parse('*italic*');

        $this->assertSame('<em>italic</em>', $html);
    }
}
