<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\StrikethroughRule;
use Tempest\Markdown\Tokens\StrikethroughToken;

class StrikethroughRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new StrikethroughRule()])->lex('~~strikethrough~~')[0];

        $this->assertEquals(new StrikethroughToken('strikethrough'), $token);
    }

    #[Test]
    public function test_lex_single_tilde(): void
    {
        $token = new Lexer([new StrikethroughRule()])->lex('~strikethrough~')[0];

        $this->assertEquals(new StrikethroughToken('strikethrough'), $token);
    }
}
