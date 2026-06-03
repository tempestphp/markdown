<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\ParserRules\NewLineRule;
use Tempest\Markdown\Parser;

class NewLineRuleTest extends TestCase
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
