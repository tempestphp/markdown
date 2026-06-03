<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\FrontMatterToken;

class FrontMatterTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new FrontMatterToken(['foo' => 'bar']);

        $this->assertEquals('', $token->parse(new Parser()));
    }
}
