<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ThinRulerRule;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;

class ThinRulerRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ThinRulerRule()])->lex('---')[0];

        $this->assertEquals(new RulerToken('---', RulerType::THIN), $token);
    }

    #[Test]
    public function test_lex_long(): void
    {
        $token = new Lexer([new ThinRulerRule()])->lex('-----')[0];

        $this->assertEquals(new RulerToken('-----', RulerType::THIN), $token);
    }
}
