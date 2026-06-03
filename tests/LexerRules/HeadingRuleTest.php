<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\Parser;

class HeadingRuleTest extends TestCase
{
    #[Test]
    public function test_lex_h1(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('# Hello');

        $this->assertSame('<h1 id="hello">Hello</h1>', $html);
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HeadingRule()])->parse('### Hello');

        $this->assertSame('<h3 id="hello">Hello</h3>', $html);
    }
}
