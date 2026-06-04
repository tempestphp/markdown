<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\CodeRule;
use Tempest\Markdown\Tests\ParserTestCase;

class CodeRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new CodeRule()])->parse('`code`');

        $this->assertSame('<code>code</code>', $html);
    }

    #[Test]
    public function test_lex_with_language(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new CodeRule()])->parse('`{php}code`');

        $this->assertSame('<code class="language-php">code</code>', $html);
    }

    #[Test]
    public function test_with_custom_hl_token(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new CodeRule()])->parse('`{:hl-class:code:}`');

        $this->assertSame('<code>{:hl-class:code:}</code>', $html);
    }
}
