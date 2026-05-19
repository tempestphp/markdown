<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\LinkRule;

class LinkRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<a href="#">click here</a>', (string) new Parser([new LinkRule()])->parse('[click here](#)'));
    }

    #[Test]
    public function test_lex_without_href(): void
    {
        $this->assertSame('<a href="">click here</a>', (string) new Parser([new LinkRule()])->parse('[click here]'));
    }
}
