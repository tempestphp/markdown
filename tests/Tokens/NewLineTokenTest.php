<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\NewLineToken;

class NewLineTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new NewLineToken("\n\n");

        $this->assertEquals("\n\n", $token->parse(new Parser()));
    }
}
