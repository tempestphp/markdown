<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\NewLineRule;
use Tempest\Markdown\Tests\ParserTestCase;

class NewLineRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule()])->parse("\n");

        $this->assertSame("\n", $html);
    }

    #[Test]
    public function test_lex_multiple_newlines(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule()])->parse("\n\n\n");

        $this->assertSame("\n\n\n", $html);
    }
}
