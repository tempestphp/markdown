<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\HeadingRule;

class HeadingRuleTest extends TestCase
{
    #[Test]
    public function test_lex_h1(): void
    {
        $this->assertSame('<h1 id="hello">Hello</h1>', (string) new Parser([new HeadingRule()])->parse('# Hello'));
    }

    #[Test]
    public function test_lex_h2(): void
    {
        $this->assertSame('<h2 id="hello">Hello</h2>', (string) new Parser([new HeadingRule()])->parse('## Hello'));
    }

    #[Test]
    public function test_lex_deep_heading(): void
    {
        $this->assertSame('<h3 id="hello">Hello</h3>', (string) new Parser([new HeadingRule()])->parse('### Hello'));
    }

    #[Test]
    public function test_lex_h6(): void
    {
        $this->assertSame('<h6 id="hello-world">Hello World</h6>', (string) new Parser([new HeadingRule()])->parse('###### Hello World'));
    }
}
