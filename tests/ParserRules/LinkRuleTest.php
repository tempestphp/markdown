<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\LinkRule;

class LinkRuleTest extends TestCase
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
}
