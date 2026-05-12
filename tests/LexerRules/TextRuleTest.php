<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\TextRule;
use Tempest\Markdown\Tokens\BoldToken;
use Tempest\Markdown\Tokens\TextToken;

class TextRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new TextRule()])->lex('hello')[0];

        $this->assertEquals(new TextToken('hello'), $token);
    }

    #[Test]
    public function test_lex_appends_to_previous_text_token(): void
    {
        $tokens = new Lexer([new BoldRule(), new TextRule()])->lex('Hello **world**!');

        $this->assertEquals(new TextToken('Hello '), $tokens[0]);
        $this->assertEquals(new BoldToken('world'), $tokens[1]);
        $this->assertEquals(new TextToken('!'), $tokens[2]);
    }
}
