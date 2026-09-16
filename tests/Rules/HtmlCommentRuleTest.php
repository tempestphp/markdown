<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\HtmlCommentRule;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Tests\ParserTestCase;

class HtmlCommentRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HtmlCommentRule()])->parse(
                '<!-- comment -->',
            );

        $this->assertSame('<!-- comment -->', $html);
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $comment = "<!--\nmultiline\ncomment\n-->";

        $html =
            (string) new Parser(highlighter: null, rules: [new HtmlCommentRule()])->parse(
                $comment,
            );

        $this->assertSame($comment, $html);
    }

    #[Test]
    public function lex_a_body_holding_a_dash(): void
    {
        $parser = new Parser(highlighter: null, rules: [new HtmlCommentRule()]);

        $this->assertSame(
            '<!--start-show-more-->',
            (string) $parser->parse('<!--start-show-more-->'),
        );
        $this->assertSame(
            '<!-- a - b -->',
            (string) $parser->parse('<!-- a - b -->'),
        );
        $this->assertSame(
            '<!-- a > b < c -->',
            (string) $parser->parse('<!-- a > b < c -->'),
        );
    }

    #[Test]
    public function lex_an_unterminated_comment(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new HtmlCommentRule()])->parse(
                '<!-- never closed',
            );

        $this->assertSame('<!-- never closed', $html);
    }

    #[Test]
    public function test_lex_with_surrounding_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new NewLineRule(),
            new HtmlCommentRule(),
            new ParagraphRule(),
        ])->parse("Hello\n\n<!-- comment -->\n\nWorld");

        $this->assertSame(
            "<p>Hello</p>\n\n<!-- comment -->\n\n<p>World</p>",
            $html,
        );
    }
}
