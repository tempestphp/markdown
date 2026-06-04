<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HeadingRuleTest extends ParserTestCase
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
