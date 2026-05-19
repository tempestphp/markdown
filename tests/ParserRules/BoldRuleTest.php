<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\BoldRule;

class BoldRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<strong>bold</strong>', (string) new Parser([new BoldRule()])->parse('**bold**'));
    }

    #[Test]
    public function test_lex_single_asterisk(): void
    {
        $this->assertSame('<strong>bold</strong>', (string) new Parser([new BoldRule()])->parse('*bold*'));
    }
}
