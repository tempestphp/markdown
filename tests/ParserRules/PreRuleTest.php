<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\PreRule;

class PreRuleTest extends TestCase
{
    #[Test]
    public function test_lex_with_language(): void
    {
        $this->assertSame(
            '<pre><code class="language-php"><span class="hl-keyword">echo</span> <span class="hl-value">&quot;hi&quot;</span>;</code></pre>',
            (string) new Parser([new PreRule()])->parse(<<<'MD'
            ```php
            echo "hi";
            ```
            MD),
        );
    }

    #[Test]
    public function test_lex_without_language(): void
    {
        $this->assertSame(
            '<pre><code class="language-txt">echo &quot;hi&quot;;</code></pre>',
            (string) new Parser([new PreRule()])->parse(<<<'MD'
            ```
            echo "hi";
            ```
            MD),
        );
    }

    #[Test]
    public function test_lex_preserves_significant_whitespace(): void
    {
        $this->assertSame(
            '<pre><code class="language-txt">  keep  </code></pre>',
            (string) new Parser([new PreRule()])->parse("```\n  keep  \n```"),
        );
    }

    #[Test]
    public function test_lex_with_backtick_in_content(): void
    {
        $this->assertSame(
            '<pre><code class="language-php"><span class="hl-keyword">echo</span> `uname`;</code></pre>',
            (string) new Parser([new PreRule()])->parse(<<<'MD'
            ```php
            echo `uname`;
            ```
            MD),
        );
    }

    #[Test]
    public function test_parse_without_highlighter(): void
    {
        $this->assertSame(
            '<pre><code>echo "hi";</code></pre>',
            (string) new Parser([new PreRule()], highlighter: null)->parse("```\necho \"hi\";\n```"),
        );
    }
}
