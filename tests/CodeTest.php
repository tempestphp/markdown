<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class CodeTest extends TestCase
{
    #[Test]
    public function inline_code_without_language_uses_txt_fallback_with_default_highlighter(): void
    {
        // Default highlighter has fallback "txt"; content passes through unchanged.
        $parser = new Parser();
        $this->assertSame(
            '<p><code class="language-txt">$foo</code></p>',
            $parser->parse('`$foo`')->html,
        );
    }

    #[Test]
    public function inline_code_with_explicit_language_emits_language_class(): void
    {
        // Without highlighter, no syntax highlighting is applied but the class is still set.
        $parser = new Parser(highlighter: null);
        $this->assertSame(
            '<p><code class="language-php">echo "hi";</code></p>',
            $parser->parse('`{php}echo "hi";`')->html,
        );
    }

    #[Test]
    public function inline_code_without_highlighter_omits_language_class(): void
    {
        // Without highlighter and without explicit language, no class is emitted.
        $parser = new Parser(highlighter: null);
        $this->assertSame(
            '<p><code>code</code></p>',
            $parser->parse('`code`')->html,
        );
    }
}
