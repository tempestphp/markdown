<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;

class RulerTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse_thin(): void
    {
        $token = new RulerToken('---', RulerType::THIN);

        $this->assertEquals('<hr/>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_thick(): void
    {
        $token = new RulerToken('===', RulerType::THICK);

        $this->assertEquals('<hr/>', $token->parse(new Parser()));
    }
}
