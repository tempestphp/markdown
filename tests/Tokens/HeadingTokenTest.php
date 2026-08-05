<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\HeadingToken;

class HeadingTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse_h1(): void
    {
        $token = new HeadingToken('Hello', 1, 'hello');

        $this->assertEquals('<h1 id="hello">Hello</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_h2(): void
    {
        $token = new HeadingToken('Hello', 2, 'hello');

        $this->assertEquals('<h2 id="hello">Hello</h2>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_h6(): void
    {
        $token = new HeadingToken('Hello World', 6, 'hello-world');

        $this->assertEquals('<h6 id="hello-world">Hello World</h6>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_escapes_quotes_in_id(): void
    {
        $token = new HeadingToken('h', 1, 'h-" onclick="alert(1)');

        $this->assertEquals('<h1 id="h-&quot; onclick=&quot;alert(1)">h</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold(): void
    {
        $token = new HeadingToken('Hello, **world**!', 1);

        $this->assertEquals('<h1>Hello, <strong>world</strong>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_italic(): void
    {
        $token = new HeadingToken('Hello, _world_!', 1);

        $this->assertEquals('<h1>Hello, <em>world</em>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_strikethrough(): void
    {
        $token = new HeadingToken('Hello, ~~world~~!', 1);

        $this->assertEquals('<h1>Hello, <s>world</s>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_link(): void
    {
        $token = new HeadingToken('Hello, [world](#)!', 1);

        $this->assertEquals('<h1>Hello, <a href="#">world</a>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_code(): void
    {
        $token = new HeadingToken('Hello, `world`!', 1);

        $this->assertEquals('<h1>Hello, <code class="language-txt">world</code>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_bold_and_italic(): void
    {
        $token = new HeadingToken('Hello, ***world***!', 1);

        $this->assertEquals('<h1>Hello, <strong><em>world</em></strong>!</h1>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_inline_formatting_rule_priority(): void
    {
        $parser = new Parser();

        $this->assertEquals('<h1><strong><em>text</em></strong></h1>', new HeadingToken('***text***', 1)->parse($parser));
        $this->assertEquals('<h1><strong>text</strong></h1>', new HeadingToken('**text**', 1)->parse($parser));
        $this->assertEquals('<h1><em>text</em></h1>', new HeadingToken('*text*', 1)->parse($parser));
        $this->assertEquals('<h1><strong><em>text</em></strong></h1>', new HeadingToken('___text___', 1)->parse($parser));
        $this->assertEquals('<h1><strong>text</strong></h1>', new HeadingToken('__text__', 1)->parse($parser));
        $this->assertEquals('<h1><em>text</em></h1>', new HeadingToken('_text_', 1)->parse($parser));
    }
}
