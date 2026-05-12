<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\Tokens\ItalicToken;

class ItalicRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new ItalicRule()])->lex('__italic__')[0];

        $this->assertEquals(new ItalicToken('italic'), $token);
    }

    #[Test]
    public function test_lex_single_underscore(): void
    {
        $token = new Lexer([new ItalicRule()])->lex('_italic_')[0];

        $this->assertEquals(new ItalicToken('italic'), $token);
    }
}
