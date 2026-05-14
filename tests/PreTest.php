<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class PreTest extends TestCase
{
    #[Test]
    public function fenced_code_with_explicit_language_no_highlighter(): void
    {
        $parser = new Parser(highlighter: null);
        $input = "```php\necho \"hi\";\n```";
        $this->assertSame(
            '<pre><code class="language-php">echo "hi";</code></pre>',
            $parser->parse($input)->html,
        );
    }

    #[Test]
    public function fenced_code_without_language_no_highlighter(): void
    {
        // Without highlighter and without explicit language, no class is emitted.
        $parser = new Parser(highlighter: null);
        $input = "```\necho \"hi\";\n```";
        $this->assertSame(
            '<pre><code>echo "hi";</code></pre>',
            $parser->parse($input)->html,
        );
    }

    #[Test]
    public function fenced_code_without_language_uses_txt_fallback_with_default_highlighter(): void
    {
        // Default highlighter's fallback is "txt"; non-php content passes through escaped by the highlighter.
        $parser = new Parser();
        $input = "```\necho \"hi\";\n```";
        $this->assertSame(
            '<pre><code class="language-txt">echo &quot;hi&quot;;</code></pre>',
            $parser->parse($input)->html,
        );
    }
}
