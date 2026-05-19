<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ItalicRule;

class ItalicRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<em>italic</em>', (string) new Parser([new ItalicRule()])->parse('__italic__'));
    }

    #[Test]
    public function test_lex_single_underscore(): void
    {
        $this->assertSame('<em>italic</em>', (string) new Parser([new ItalicRule()])->parse('_italic_'));
    }
}
