<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\FrontMatterToken;

class FrontMatterTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new FrontMatterToken(['foo' => 'bar']);

        $this->assertEquals('', $token->parse(new Parser()));
    }
}
