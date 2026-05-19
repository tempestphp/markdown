<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\NewLineRule;

class NewLineRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame("\n", (string) new Parser([new NewLineRule()])->parse("\n"));
    }

    #[Test]
    public function test_lex_multiple_newlines(): void
    {
        $this->assertSame("\n\n\n", (string) new Parser([new NewLineRule()])->parse("\n\n\n"));
    }
}
