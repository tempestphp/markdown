<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\ParserRules\ThickRulerRule;
use Tempest\Markdown\Parser;

class ThickRulerRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ThickRulerRule()])->parse('===');

        $this->assertSame('<hr/>', $html);
    }

    #[Test]
    public function test_lex_long(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ThickRulerRule()])->parse('=====');

        $this->assertSame('<hr/>', $html);
    }
}
