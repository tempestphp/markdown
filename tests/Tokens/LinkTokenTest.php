<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\LinkToken;

class LinkTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new LinkToken('click here', '#');

        $this->assertEquals('<a href="#">click here</a>', $token->parse(new Parser()));
    }
}
