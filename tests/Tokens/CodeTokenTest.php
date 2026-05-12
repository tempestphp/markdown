<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\CodeToken;

class CodeTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new CodeToken('$foo');

        $this->assertEquals('<code>$foo</code>', $token->parse(new Parser()));
    }
}
