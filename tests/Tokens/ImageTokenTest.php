<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\ImageToken;

class ImageTokenTest extends TestCase
{
    #[Test]
    public function test_parse_with_alt(): void
    {
        $token = new ImageToken('https://example.com/img.png', 'a cat');

        $this->assertEquals('<img src="https://example.com/img.png" alt="a cat">', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_without_alt(): void
    {
        $token = new ImageToken('https://example.com/img.png', null);

        $this->assertEquals('<img src="https://example.com/img.png">', $token->parse(new Parser()));
    }
}
