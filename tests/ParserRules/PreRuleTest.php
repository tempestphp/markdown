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
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```php
        echo "hi";
        ```
        MD);

        $this->assertSame('<pre class="language-php">echo "hi";</pre>', $html);
    }

    #[Test]
    public function test_lex_with_language_and_path(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```php file.php
        echo "hi";
        ```
        MD);

        $this->assertSame('<pre class="language-php">echo "hi";</pre>', $html);
    }

    #[Test]
    public function test_lex_without_language(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(<<<'MD'
        ```
        echo "hi";
        ```
        MD);

        $this->assertSame('<pre>echo "hi";</pre>', $html);
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
