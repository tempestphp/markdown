<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\PreRule;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class PreRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_with_language(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new PreRule(),
            new TextRule(),
        ])->parse(
            <<<'MD'
            ```php
            echo "hi";
            ```
            MD,
        );

        $this->assertSame(
            '<pre class="language-php">echo &quot;hi&quot;;</pre>',
            $html,
        );
    }

    #[Test]
    public function test_lex_with_language_and_title(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new PreRule(),
            new TextRule(),
        ])->parse(
            <<<'MD'
            ```php file.php
            echo "hi";
            ```
            MD,
        );

        $this->assertSame(
            '<div class="code-title">file.php</div><pre class="language-php">echo &quot;hi&quot;;</pre>',
            $html,
        );
    }

    #[Test]
    public function test_lex_without_language(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new PreRule(),
            new TextRule(),
        ])->parse(
            <<<'MD'
            ```
            echo "hi";
            ```
            MD,
        );

        $this->assertSame('<pre>echo &quot;hi&quot;;</pre>', $html);
    }

    #[Test]
    public function test_lex_preserves_significant_whitespace(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new PreRule(),
            new TextRule(),
        ])->parse(
            "```\n  keep  \n```",
        );

        $this->assertSame('<pre>  keep  </pre>', $html);
    }

    #[Test]
    public function test_lex_with_backtick_in_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new PreRule(),
            new TextRule(),
        ])->parse(
            <<<'MD'
            ```php
            echo `uname`;
            ```
            MD,
        );

        $this->assertSame(
            '<pre class="language-php">echo `uname`;</pre>',
            $html,
        );
    }

    #[Test]
    public function lex_tilde_fence(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(
                "~~~\ncode\n~~~",
            );

        $this->assertSame('<pre>code</pre>', $html);
    }

    #[Test]
    public function lex_tilde_fence_with_language(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new PreRule()])->parse(
                "~~~php\n\$a = 1;\n~~~",
            );

        $this->assertSame('<pre class="language-php">$a = 1;</pre>', $html);
    }

    #[Test]
    public function lex_longer_fence_may_contain_a_shorter_one(): void
    {
        $parser = new Parser(highlighter: null, rules: [new PreRule()]);

        $this->assertSame(
            '<pre>a ``` b</pre>',
            (string) $parser->parse("````\na ``` b\n````"),
        );

        $this->assertSame(
            '<pre>a ~~~ b</pre>',
            (string) $parser->parse("~~~~\na ~~~ b\n~~~~"),
        );
    }

    #[Test]
    public function lex_does_not_take_over_strikethrough(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new PreRule(),
            new StrikethroughRule(),
            new TextRule(),
        ])->parse('a ~~b~~ c');

        $this->assertSame('a <s>b</s> c', $html);
    }
}
