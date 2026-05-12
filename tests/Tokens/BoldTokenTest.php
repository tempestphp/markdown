<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\BoldToken;

class BoldTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new BoldToken('world');

        $this->assertEquals('<strong>world</strong>', $token->parse(new Parser()));
    }
}
