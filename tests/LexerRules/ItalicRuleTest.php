<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\ParserRules\ItalicRule;
use Tempest\Markdown\Parser;

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
