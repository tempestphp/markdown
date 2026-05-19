<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\DivRule;

class DivRuleTest extends TestCase
{
    #[Test]
    public function test_lex_without_class(): void
    {
        $this->assertSame("<div>Hello\n</div>", (string) new Parser([new DivRule()])->parse(":::\nHello\n:::\n"));
    }

    #[Test]
    public function test_lex_with_class(): void
    {
        $this->assertSame("<div class=\"warning\">Hello\n</div>", (string) new Parser([new DivRule()])->parse(":::warning\nHello\n:::\n"));
    }

    #[Test]
    public function test_lex_with_multiple_classes(): void
    {
        $this->assertSame("<div class=\"foo bar\">Hello\n</div>", (string) new Parser([new DivRule()])->parse(":::foo bar\nHello\n:::\n"));
    }

    #[Test]
    public function test_lex_multiline_content(): void
    {
        $this->assertSame("<div class=\"warning\">line one\nline two\n</div>", (string) new Parser([new DivRule()])->parse(":::warning\nline one\nline two\n:::\n"));
    }
}
