<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\Tokens\ParagraphToken;

class ParagraphRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ParagraphRule()])->lex("Hello, world!\n")[0];

        $this->assertEquals(new ParagraphToken("Hello, world!\n"), $token);
    }
}
