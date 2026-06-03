<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ThickRulerRule;

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
