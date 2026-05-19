<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\CodeRule;

class CodeRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<code class="language-txt">code</code>', (string) new Parser([new CodeRule()])->parse('`code`'));
    }

    #[Test]
    public function test_lex_with_language(): void
    {
        $this->assertSame(
            '<code class="language-php"><span class="hl-keyword">echo</span> <span class="hl-value">&quot;hi&quot;</span>;</code>',
            (string) new Parser([new CodeRule()])->parse('`{php}echo "hi";`'),
        );
    }

    #[Test]
    public function test_parse_with_language_without_highlighter(): void
    {
        $this->assertSame(
            '<code class="language-php">echo "hi";</code>',
            (string) new Parser([new CodeRule()], highlighter: null)->parse('`{php}echo "hi";`'),
        );
    }
}
