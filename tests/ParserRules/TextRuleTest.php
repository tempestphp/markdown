<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;
use Tempest\Markdown\ParserRules\TextRule;

class TextRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('hello', (string) new Parser([new TextRule()])->parse('hello'));
    }

    #[Test]
    public function test_lex_appends_to_previous_text_token(): void
    {
        $this->assertSame('Hello <strong>world</strong>!', (string) new Parser([new BoldRule(), new TextRule()])->parse('Hello **world**!'));
    }
}
