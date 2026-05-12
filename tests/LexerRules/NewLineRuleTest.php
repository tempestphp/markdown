<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\Tokens\NewLineToken;

class NewLineRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new NewLineRule()])->lex("\n")[0];

        $this->assertEquals(new NewLineToken("\n"), $token);
    }

    #[Test]
    public function test_lex_multiple_newlines(): void
    {
        $token = new Lexer([new NewLineRule()])->lex("\n\n\n")[0];

        $this->assertEquals(new NewLineToken("\n\n\n"), $token);
    }
}
