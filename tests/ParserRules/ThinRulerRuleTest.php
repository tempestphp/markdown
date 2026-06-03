<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ThinRulerRule;
use Tempest\Markdown\Tests\ParserTestCase;

class ThinRulerRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ThinRulerRule()])->parse('---');

        $this->assertSame('<hr/>', $html);
    }

    #[Test]
    public function test_lex_long(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new ThinRulerRule()])->parse('-----');

        $this->assertSame('<hr/>', $html);
    }
}
