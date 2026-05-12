<?php

namespace Tempest\Markdown\Tests\Lexer\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\CodeRule;
use Tempest\Markdown\Tokens\CodeToken;

class CodeRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new CodeRule()])->lex('`code`')[0];

        $this->assertEquals(new CodeToken('code'), $token);
    }
}
