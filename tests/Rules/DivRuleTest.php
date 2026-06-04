<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\DivRule;
use Tempest\Markdown\Tests\ParserTestCase;

class DivRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_without_class(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new DivRule()])->parse(":::\nHello\n:::\n");

        $this->assertSame("<div>Hello\n</div>", $html);
    }

    #[Test]
    public function test_lex_with_class(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new DivRule()])->parse(":::warning\nHello\n:::\n");

        $this->assertSame("<div class=\"warning\">Hello\n</div>", $html);
    }

    #[Test]
    public function test_lex_with_multiple_classes(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new DivRule()])->parse(":::foo bar\nHello\n:::\n");

        $this->assertSame("<div class=\"foo bar\">Hello\n</div>", $html);
    }

    #[Test]
    public function test_lex_multiline_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new DivRule()])->parse(":::warning\nline one\nline two\n:::\n");

        $this->assertSame("<div class=\"warning\">line one\nline two\n</div>", $html);
    }
}
