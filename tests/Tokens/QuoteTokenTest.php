<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\QuoteToken;

class QuoteTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new QuoteToken('Hello', 1);

        $this->assertEquals('<blockquote>Hello</blockquote>', $token->parse(new Parser()));
    }
}
