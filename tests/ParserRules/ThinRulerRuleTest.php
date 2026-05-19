<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ThinRulerRule;

class ThinRulerRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<hr/>', (string) new Parser([new ThinRulerRule()])->parse('---'));
    }

    #[Test]
    public function test_lex_long(): void
    {
        $this->assertSame('<hr/>', (string) new Parser([new ThinRulerRule()])->parse('-----'));
    }
}
