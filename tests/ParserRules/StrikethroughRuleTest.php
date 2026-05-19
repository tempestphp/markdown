<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\StrikethroughRule;

class StrikethroughRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<s>strikethrough</s>', (string) new Parser([new StrikethroughRule()])->parse('~~strikethrough~~'));
    }

    #[Test]
    public function test_lex_single_tilde(): void
    {
        $this->assertSame('<s>strikethrough</s>', (string) new Parser([new StrikethroughRule()])->parse('~strikethrough~'));
    }
}
