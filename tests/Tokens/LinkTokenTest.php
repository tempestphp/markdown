<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\LinkToken;

class LinkTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new LinkToken('click here', '#');

        $this->assertEquals('<a href="#">click here</a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_text(): void
    {
        $token = new LinkToken('click **here**', '#');

        $this->assertEquals('<a href="#">click <strong>here</strong></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic_text(): void
    {
        $token = new LinkToken('click _here_', '#');

        $this->assertEquals('<a href="#">click <em>here</em></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough_text(): void
    {
        $token = new LinkToken('click ~~here~~', '#');

        $this->assertEquals('<a href="#">click <s>here</s></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $token = new LinkToken('![alt](/image.jpg)', '/link');

        $this->assertEquals('<a href="/link"><img src="/image.jpg" alt="alt"></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_target_blank_text(): void
    {
        $token = new LinkToken('click here', '*https://tempestphp.com');

        $this->assertEquals('<a href="https://tempestphp.com" target="_blank" rel="noopener noreferrer">click here</a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_and_italic_text(): void
    {
        $token = new LinkToken('click ***here***', '#');

        $this->assertEquals('<a href="#">click <strong><em>here</em></strong></a>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<a href="#"><strong><em>text</em></strong></a>', new LinkToken('***text***', '#')->parse($parser));
        $this->assertEquals('<a href="#"><strong>text</strong></a>', new LinkToken('**text**', '#')->parse($parser));
        $this->assertEquals('<a href="#"><em>text</em></a>', new LinkToken('*text*', '#')->parse($parser));
        $this->assertEquals('<a href="#"><strong><em>text</em></strong></a>', new LinkToken('___text___', '#')->parse($parser));
        $this->assertEquals('<a href="#"><strong>text</strong></a>', new LinkToken('__text__', '#')->parse($parser));
        $this->assertEquals('<a href="#"><em>text</em></a>', new LinkToken('_text_', '#')->parse($parser));
    }
}
