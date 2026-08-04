<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\CodeToken;

class CodeTokenTest extends ParserTestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new CodeToken(null, '$foo');

        $this->assertEquals('<code class="language-txt">$foo</code>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_language(): void
    {
        $token = new CodeToken('php', 'echo "hi";');

        $this->assertEquals('<code class="language-php"><span class="hl-keyword">echo</span> <span class="hl-value">&quot;hi&quot;</span>;</code>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_with_language_without_highlighter(): void
    {
        $token = new CodeToken('php', 'echo "hi";');

        $this->assertEquals('<code class="language-php">echo "hi";</code>', $token->parse(new Parser(highlighter: null)));
    }

    #[Test]
    public function test_parse_resolves_unknown_language_to_fallback(): void
    {
        $token = new CodeToken('a"onmouseover="alert(1)', 'code');

        $this->assertEquals('<code class="language-txt">code</code>', $token->parse(new Parser()));
    }

    #[Test]
    public function test_parse_escapes_quotes_in_language(): void
    {
        $token = new CodeToken('a"onmouseover="alert(1)', 'code');

        $this->assertEquals('<code class="language-a&quot;onmouseover=&quot;alert(1)">code</code>', $token->parse(new Parser(highlighter: null)));
    }
}
