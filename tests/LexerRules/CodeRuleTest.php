<?php

namespace Tempest\Markdown\Tests\LexerRules;

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

        $this->assertEquals(new CodeToken(null, 'code'), $token);
    }

    #[Test]
    public function test_lex_with_language(): void
    {
        $token = new Lexer([new CodeRule()])->lex('`{php}code`')[0];

        $this->assertEquals(new CodeToken('php', 'code'), $token);
    }

    #[Test]
    public function test_with_custom_hl_token(): void
    {
        $token = new Lexer([new CodeRule()])->lex('`{:hl-class:code:}`')[0];

        $this->assertEquals(new CodeToken(language: null, content: '{:hl-class:code:}'), $token);
    }
}
