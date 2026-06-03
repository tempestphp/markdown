<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class TextRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TextRule()])->parse('hello');

        $this->assertSame('hello', $html);
    }

    #[Test]
    public function test_lex_appends_to_previous_text_token(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new BoldRule(), new TextRule()])->parse('Hello **world**!');

        $this->assertSame('Hello <strong>world</strong>!', $html);
    }
}
