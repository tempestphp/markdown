<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\BoldAndItalicRule;
use Tempest\Markdown\LexerRules\BoldRule;
use Tempest\Markdown\LexerRules\ItalicRule;
use Tempest\Markdown\Tokens\BoldAndItalicToken;

class BoldAndItalicRuleTest extends TestCase
{
    #[Test]
    public function test_triple_asterisk_bold_and_italic(): void
    {
        $tokens = new Lexer([new BoldAndItalicRule(), new BoldRule(), new ItalicRule()])->lex('***text***');

        $this->assertEquals(new BoldAndItalicToken('text'), $tokens[0]);
    }

    #[Test]
    public function test_triple_underscore_bold_and_italic(): void
    {
        $tokens = new Lexer([new BoldAndItalicRule(), new BoldRule(), new ItalicRule()])->lex('___text___');

        $this->assertEquals(new BoldAndItalicToken('text'), $tokens[0]);
    }

    #[Test]
    public function test_does_not_lex_double_asterisk(): void
    {
        $tokens = new Lexer([new BoldAndItalicRule()])->lex('**text**');

        $this->assertCount(0, $tokens);
    }

    #[Test]
    public function test_does_not_lex_single_asterisk(): void
    {
        $tokens = new Lexer([new BoldAndItalicRule()])->lex('*text*');

        $this->assertCount(0, $tokens);
    }

    #[Test]
    public function test_does_not_lex_double_underscore(): void
    {
        $tokens = new Lexer([new BoldAndItalicRule()])->lex('__text__');

        $this->assertCount(0, $tokens);
    }
}
