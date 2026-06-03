<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\StrikethroughRule;
use Tempest\Markdown\Parser;

class StrikethroughRuleTest extends TestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new StrikethroughRule()])->parse('~~strikethrough~~');

        $this->assertSame('<s>strikethrough</s>', $html);
    }

    #[Test]
    public function test_lex_single_tilde(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new StrikethroughRule()])->parse('~strikethrough~');

        $this->assertSame('<s>strikethrough</s>', $html);
    }
}
