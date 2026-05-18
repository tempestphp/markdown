<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\TokenCollection;
use Tempest\Markdown\Tokens\TextToken;

final class TokenCollectionTest extends TestCase
{
    #[Test]
    public function test_array_access_append_uses_next_numeric_index(): void
    {
        $collection = new TokenCollection();
        $token = new TextToken('x');

        $collection[] = $token;

        $this->assertSame($token, $collection[0]);
    }
}
