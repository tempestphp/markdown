<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Parser;

class TextRuleTest extends TestCase
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
