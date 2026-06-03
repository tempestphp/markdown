<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\HtmlCommentRule;
use Tempest\Markdown\ParserRules\NewLineRule;
use Tempest\Markdown\ParserRules\ParagraphRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HtmlCommentRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new HtmlCommentRule()])->parse('<!-- comment -->');

        $this->assertSame('<!-- comment -->', $html);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $comment = "<!--\nmultiline\ncomment\n-->";

        $html = (string) new Parser(highlighter: null, rules: [new HtmlCommentRule()])->parse($comment);

        $this->assertSame($comment, $html);
    }

    #[Test]
    public function test_lex_with_surrounding_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new NewLineRule(), new HtmlCommentRule(), new ParagraphRule()])->parse("Hello\n\n<!-- comment -->\n\nWorld");

        $this->assertSame("<p>Hello</p>\n\n<!-- comment -->\n\n<p>World</p>", $html);
    }
}
