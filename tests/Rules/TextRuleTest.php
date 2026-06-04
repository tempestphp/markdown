<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\BoldRule;
use Tempest\Markdown\Rules\TextRule;
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
