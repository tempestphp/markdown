<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\PreRule;
use Tempest\Markdown\Tests\ParserTestCase;

class PreRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_with_language(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```php
        echo "hi";
        ```
        MD);

        $this->assertSame('<pre class="language-php">echo &quot;hi&quot;;</pre>', $html);
    }

    #[Test]
    public function test_lex_with_language_and_title(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```php file.php
        echo "hi";
        ```
        MD);

        $this->assertSame('<div class="code-title">file.php</div><pre class="language-php">echo &quot;hi&quot;;</pre>', $html);
    }

    #[Test]
    public function test_lex_without_language(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```
        echo "hi";
        ```
        MD);

        $this->assertSame('<pre>echo &quot;hi&quot;;</pre>', $html);
    }

    #[Test]
    public function test_lex_preserves_significant_whitespace(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse("```\n  keep  \n```");

        $this->assertSame('<pre>  keep  </pre>', $html);
    }

    #[Test]
    public function test_lex_with_backtick_in_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```php
        echo `uname`;
        ```
        MD);

        $this->assertSame('<pre class="language-php">echo `uname`;</pre>', $html);
    }
}
