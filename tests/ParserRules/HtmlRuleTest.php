<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\HtmlRule;
use Tempest\Markdown\ParserRules\NewLineRule;
use Tempest\Markdown\ParserRules\ParagraphRule;
use Tempest\Markdown\ParserRules\TextRule;

class HtmlRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $this->assertSame('<p>Hi</p>', (string) new Parser([new HtmlRule()])->parse('<p>Hi</p>'));
    }

    #[Test]
    public function test_lex_nested(): void
    {
        $this->assertSame('<div><div>Hi</div></div>', (string) new Parser([new HtmlRule()])->parse('<div><div>Hi</div></div>'));
    }

    #[Test]
    public function test_lex_multiline(): void
    {
        $html = <<<'HTML'
        Hello
        <p>
        Hi
        </p>
        World
        HTML;

        $result = new Parser([new NewLineRule(), new HtmlRule(), new ParagraphRule()])->parse($html);

        $this->assertStringContainsString("<p>\nHi\n</p>\n", $result->html);
    }

    #[Test]
    public function test_void_tags(): void
    {
        $html = '<area><base><br><col><embed><hr><img><input><link><meta><param><source><track><wbr>Hello';

        $this->assertSame($html, (string) new Parser([new HtmlRule(), new TextRule()])->parse($html));
    }

    #[Test]
    public function test_void_tags_are_case_insensitive(): void
    {
        $result = new Parser([new HtmlRule(), new NewLineRule(), new ParagraphRule()])->parse("<BR>\nHello");

        $this->assertSame('<BR>' . "\n" . '<p>Hello</p>', $result->html);
    }
}
