<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\PreToken;

class PreTokenTest extends TestCase
{
    #[Test]
    public function test_parse_with_language(): void
    {
        $token = new PreToken(language: 'php', content: 'echo "hi";');

        $this->assertEquals(
            '<pre><code class="language-php"><span class="hl-keyword">echo</span> <span class="hl-value">&quot;hi&quot;</span>;</code></pre>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_without_language(): void
    {
        $token = new PreToken(language: null, content: 'echo "hi";');

        $this->assertEquals(
            '<pre><code class="language-txt">echo &quot;hi&quot;;</code></pre>',
            $token->parse(new Parser()),
        );
    }
}
