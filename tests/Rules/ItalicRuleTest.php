<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ItalicRule;
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
    public function test_lex_with_asterisk(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ItalicRule()])->parse('*italic*');

        $this->assertSame('<em>italic</em>', $html);
    }
}
