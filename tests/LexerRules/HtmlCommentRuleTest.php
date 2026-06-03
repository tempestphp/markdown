<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\HtmlCommentRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\Tokens\HtmlCommentToken;
use Tempest\Markdown\Tokens\ParagraphToken;

class HtmlCommentRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $token = new Lexer([new HtmlCommentRule()])->lex('<!-- comment -->')[0];

        $this->assertEquals(new HtmlCommentToken('<!-- comment -->'), $token);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $comment = "<!--\nmultiline\ncomment\n-->";

        $token = new Lexer([new HtmlCommentRule()])->lex($comment)[0];

        $this->assertEquals(new HtmlCommentToken($comment), $token);
    }

    #[Test]
    public function test_lex_with_surrounding_content(): void
    {
        $tokens = new Lexer([new NewLineRule(), new HtmlCommentRule(), new ParagraphRule()])->lex("Hello\n\n<!-- comment -->\n\nWorld");

        $this->assertCount(5, $tokens);
        $this->assertEquals(new ParagraphToken('Hello'), $tokens[0]);
        $this->assertEquals(new HtmlCommentToken('<!-- comment -->'), $tokens[2]);
        $this->assertEquals(new ParagraphToken('World'), $tokens[4]);
    }
}
