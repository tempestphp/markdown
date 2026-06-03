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

    #[Test]
    public function test_array_access_can_set_explicit_zero_offset_after_append(): void
    {
        $collection = new TokenCollection();
        $original = new TextToken('x');
        $replacement = new TextToken('y');

        $collection[] = $original;
        $collection[0] = $replacement;

        $this->assertSame($replacement, $collection[0]);
    }

    #[Test]
    public function test_index_is_correctly_updated(): void
    {
        $collection = new TokenCollection();

        $collection[] = new TextToken('x');
        $collection[1] = new TextToken('y');
        $collection[] = new TextToken('z');

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function test_offset_set_with_initial(): void
    {
        $collection = new TokenCollection([new TextToken('initial')]);
        $collection[] = new TextToken('append');

        $this->assertCount(2, $collection);
    }
}
