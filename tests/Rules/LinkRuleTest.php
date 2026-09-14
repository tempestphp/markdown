<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;

class LinkRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here](#)',
            );

        $this->assertSame('<a href="#">click here</a>', $html);
    }

    #[Test]
    public function test_lex_without_href(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here]',
            );

        $this->assertSame('<a href="">click here</a>', $html);
    }

    #[Test]
    public function test_lex_with_image_content(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[![alt](/image.jpg)](/link)',
            );

        $this->assertSame(
            '<a href="/link"><img src="/image.jpg" alt="alt"></a>',
            $html,
        );
    }

    #[Test]
    public function lex_with_parenthesis_content(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043(v=vs.100)?redirectedfrom=MSDN)',
            );

        $this->assertSame(
            '<a href="https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043(v=vs.100)?redirectedfrom=MSDN">.NET best practices</a>',
            $html,
        );
    }

    #[Test]
    public function lex_with_end_parenthesis_without_start_parenthesis(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043v=vs.100\)?redirectedfrom=MSDN)',
            );

        $this->assertSame(
            '<a href="https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043v=vs.100)?redirectedfrom=MSDN">.NET best practices</a>',
            $html,
        );
    }

    #[Test]
    public function lex_with_title(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here](/uri "Title")',
            );

        $this->assertSame(
            '<a href="/uri" title="Title">click here</a>',
            $html,
        );
    }

    #[Test]
    public function lex_with_single_quoted_and_parenthesised_title(): void
    {
        $parser = new Parser(highlighter: null, rules: [new LinkRule()]);

        $this->assertSame(
            '<a href="/uri" title="Title">click here</a>',
            (string) $parser->parse("[click here](/uri 'Title')"),
        );

        $this->assertSame(
            '<a href="/uri" title="Title">click here</a>',
            (string) $parser->parse('[click here](/uri (Title))'),
        );
    }

    #[Test]
    public function lex_with_angle_bracket_destination(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here](</my uri> "Title")',
            );

        $this->assertSame(
            '<a href="/my uri" title="Title">click here</a>',
            $html,
        );
    }

    #[Test]
    public function lex_with_empty_angle_bracket_destination(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here](<> "Title")',
            );

        $this->assertSame(
            '<a href="" title="Title">click here</a>',
            $html,
        );
    }

    #[Test]
    public function lex_decodes_entities_in_destination_and_title(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here](/a&amp;b "R&amp;D")',
            );

        $this->assertSame(
            '<a href="/a&amp;b" title="R&amp;D">click here</a>',
            $html,
        );
    }

    #[Test]
    public function lex_escapes_quotes_in_title(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
                '[click here](/uri "a \"b\"")',
            );

        $this->assertSame(
            '<a href="/uri" title="a &quot;b&quot;">click here</a>',
            $html,
        );
    }

    #[Test]
    public function lex_with_space_in_destination_stays_literal(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new LinkRule(),
            new TextRule(),
        ])->parse(
            'see [click here](/my uri) there',
        );

        $this->assertSame('see [click here](/my uri) there', $html);
    }

    #[Test]
    public function lex_with_unclosed_destination_stays_literal(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new LinkRule(),
            new TextRule(),
        ])->parse(
            '[click here](/uri',
        );

        $this->assertSame('[click here](/uri', $html);
    }
}
