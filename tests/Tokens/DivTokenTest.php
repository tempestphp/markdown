<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\DivToken;

class DivTokenTest extends TestCase
{
    #[Test]
    public function test_parse_without_class(): void
    {
        $token = new DivToken(class: null, content: 'Hello');

        $this->assertEquals('<div>Hello</div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_class(): void
    {
        $token = new DivToken(class: 'warning', content: 'Hello');

        $this->assertEquals('<div class="warning">Hello</div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_multiple_classes(): void
    {
        $token = new DivToken(class: 'foo bar', content: 'Hello');

        $this->assertEquals('<div class="foo bar">Hello</div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new DivToken(class: null, content: 'Hello **world**');

        $this->assertEquals('<div>Hello <strong>world</strong></div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new DivToken(class: null, content: 'Hello _world_');

        $this->assertEquals('<div>Hello <em>world</em></div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new DivToken(class: null, content: 'Hello [world](#)');

        $this->assertEquals('<div>Hello <a href="#">world</a></div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_heading(): void
    {
        $token = new DivToken(class: null, content: '## hi');

        $this->assertEquals('<div><h2 id="hi">hi</h2></div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_and_italic(): void
    {
        $token = new DivToken(class: null, content: 'Hello ***world***');

        $this->assertEquals('<div>Hello <strong><em>world</em></strong></div>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<div><strong><em>text</em></strong></div>', new DivToken(class: null, content: '***text***')->parse($parser));
        $this->assertEquals('<div><strong>text</strong></div>', new DivToken(class: null, content: '**text**')->parse($parser));
        $this->assertEquals('<div><em>text</em></div>', new DivToken(class: null, content: '*text*')->parse($parser));
        $this->assertEquals('<div><strong><em>text</em></strong></div>', new DivToken(class: null, content: '___text___')->parse($parser));
        $this->assertEquals('<div><strong>text</strong></div>', new DivToken(class: null, content: '__text__')->parse($parser));
        $this->assertEquals('<div><em>text</em></div>', new DivToken(class: null, content: '_text_')->parse($parser));
    }
}
