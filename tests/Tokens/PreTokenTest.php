<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\PreToken;

class PreTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse_with_language(): void
    {
        $token = new PreToken(language: 'php', content: 'echo "hi";');

        $this->assertEquals(
            '<pre class="language-php"><span class="hl-keyword">echo</span> <span class="hl-value">&quot;hi&quot;</span>;</pre>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_without_language(): void
    {
        $token = new PreToken(language: null, content: 'echo "hi";');

        $this->assertEquals(
            '<pre class="language-txt">echo &quot;hi&quot;;</pre>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_without_highlighter(): void
    {
        $token = new PreToken(language: null, content: 'echo "hi";');

        $this->assertEquals(
            '<pre>echo "hi";</pre>',
            $token->parse(new Parser(highlighter: null)),
        );
    }

    #[Test]
    public function test_parse_with_title(): void
    {
        $token = new PreToken(language: null, content: 'echo "hi";', title: 'Hello');

        $this->assertEquals(
            '<div class="code-title">Hello</div><pre class="language-txt">echo &quot;hi&quot;;</pre>',
            $token->parse(new Parser()),
        );
    }
}
