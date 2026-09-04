<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\LinkRule;
use Tempest\Markdown\Tests\ParserTestCase;

class LinkRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse('[click here](#)');

        $this->assertSame('<a href="#">click here</a>', $html);
    }

    #[Test]
    public function test_lex_without_href(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse('[click here]');

        $this->assertSame('<a href="">click here</a>', $html);
    }

    #[Test]
    public function test_lex_with_image_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse('[![alt](/image.jpg)](/link)');

        $this->assertSame('<a href="/link"><img src="/image.jpg" alt="alt"></a>', $html);
    }

    #[Test]
    public function lex_with_parenthesis_content(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
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
        $html = (string) new Parser(highlighter: null, rules: [new LinkRule()])->parse(
            '[.NET best practices](https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043v=vs.100\)?redirectedfrom=MSDN)',
        );

        $this->assertSame(
            '<a href="https://learn.microsoft.com/en-us/previous-versions/dotnet/netframework-4.0/ms229043v=vs.100)?redirectedfrom=MSDN">.NET best practices</a>',
            $html,
        );
    }
}
