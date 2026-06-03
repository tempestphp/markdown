<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ItalicRule;

class ItalicRuleTest extends TestCase
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
