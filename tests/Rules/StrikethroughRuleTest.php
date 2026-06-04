<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Tests\ParserTestCase;

class StrikethroughRuleTest extends ParserTestCase
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
