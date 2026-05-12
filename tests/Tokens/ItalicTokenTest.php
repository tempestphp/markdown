<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\ItalicToken;

class ItalicTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new ItalicToken('world');

        $this->assertEquals('<em>world</em>', $token->parse(new Parser()));
    }
}
