<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ParagraphRule;

class ParagraphRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame("<p>Hello, world!\n</p>", (string) new Parser([new ParagraphRule()])->parse("Hello, world!\n"));
    }
}
