<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ThickRulerRule;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;

class ThickRulerRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ThickRulerRule()])->lex('===')[0];

        $this->assertEquals(new RulerToken('===', RulerType::THICK), $token);
    }

    #[Test]
    public function test_lex_long(): void
    {
        $token = new Lexer([new ThickRulerRule()])->lex('=====')[0];

        $this->assertEquals(new RulerToken('=====', RulerType::THICK), $token);
    }
}
