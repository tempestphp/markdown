<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\ListToken;

class ListTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new ListToken(['item']);

        $this->assertEquals('<ul><li>item</li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_multiple_items(): void
    {
        $token = new ListToken(['one', 'two', 'three']);

        $this->assertEquals('<ul><li>one</li><li>two</li><li>three</li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new ListToken(['hello **world**']);

        $this->assertEquals('<ul><li>hello <strong>world</strong></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new ListToken(['hello __world__']);

        $this->assertEquals('<ul><li>hello <em>world</em></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new ListToken(['[world](#)']);

        $this->assertEquals('<ul><li><a href="#">world</a></li></ul>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new ListToken(['run `php artisan`']);

        $this->assertEquals('<ul><li>run <code>php artisan</code></li></ul>', $token->parse(new Parser()));
    }
}
