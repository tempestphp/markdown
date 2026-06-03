<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\TextToken;

class TextTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new TextToken('Hello, world!');

        $this->assertEquals('Hello, world!', $token->parse(new Parser()));
    }
}
