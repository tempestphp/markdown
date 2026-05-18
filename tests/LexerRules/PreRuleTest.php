<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\PreRule;
use Tempest\Markdown\Tokens\PreToken;

class PreRuleTest extends TestCase
{
    #[Test]
    public function test_lex_with_language(): void
    {
        $token = new Lexer([new PreRule()])->lex(<<<'MD'
        ```php
        echo "hi";
        ```
        MD)[0];

        $this->assertEquals(new PreToken(language: 'php', content: 'echo "hi";'), $token);
    }

    #[Test]
    public function test_lex_without_language(): void
    {
        $token = new Lexer([new PreRule()])->lex(<<<'MD'
        ```
        echo "hi";
        ```
        MD)[0];

        $this->assertEquals(new PreToken(language: null, content: 'echo "hi";'), $token);
    }

    #[Test]
    public function test_lex_preserves_significant_whitespace(): void
    {
        $token = new Lexer([new PreRule()])->lex("```\n  keep  \n```")[0];

        $this->assertEquals(new PreToken(language: null, content: '  keep  '), $token);
    }

    #[Test]
    public function test_lex_with_backtick_in_content(): void
    {
        $token = new Lexer([new PreRule()])->lex(<<<'MD'
        ```php
        echo `uname`;
        ```
        MD)[0];

        $this->assertEquals(new PreToken(language: 'php', content: 'echo `uname`;'), $token);
    }
}
