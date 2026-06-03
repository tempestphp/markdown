<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\NewLineToken;

class NewLineTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new NewLineToken("\n\n");

        $this->assertEquals("\n\n", $token->parse(new Parser()));
    }
}
