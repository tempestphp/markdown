<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\StrikethroughRule;
use Tempest\Markdown\Rules\TextRule;
use Tempest\Markdown\Tests\ParserTestCase;
use Tempest\Markdown\Tokens\TextToken;

class StrikethroughRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new StrikethroughRule(),
            new TextRule(),
        ])->parse(
            '~~strikethrough~~',
        );

        $this->assertSame('<s>strikethrough</s>', $html);
    }

    #[Test]
    public function test_lex_single_tilde(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new StrikethroughRule(),
            new TextRule(),
        ])->parse(
            '~strikethrough~',
        );

        $this->assertSame('<s>strikethrough</s>', $html);
    }

    #[Test]
    public function unmatched_tildes_remain_literal(): void
    {
        $parser = new Parser(highlighter: null, rules: [
            new StrikethroughRule(),
            new TextRule(),
        ]);

        $this->assertSame(
            'Duration: ~2h30.',
            (string) $parser->parse('Duration: ~2h30.'),
        );
        $this->assertSame(
            'About ~~5 km',
            (string) $parser->parse('About ~~5 km'),
        );
        $this->assertSame('~~~', (string) $parser->parse('~~~'));
        $this->assertSame(
            '<p>Duration: ~2h30.</p>',
            new Markdown(highlighter: null)->parse('Duration: ~2h30.')->html,
        );
    }

    #[Test]
    public function test_unmatched_run_is_consumed_as_one_literal_token(): void
    {
        $opening = str_repeat('~', 80_000);
        $parser = new Parser(highlighter: null)->setContent($opening
        . '**bold**');
        $rule = new StrikethroughRule();

        $this->assertTrue($rule->shouldParse($parser));
        $this->assertEquals(new TextToken($opening), $rule->parse($parser));
        $this->assertSame(strlen($opening), $parser->position);
        $this->assertTrue($parser->comesNext('**bold**'));
    }

    #[Test]
    public function test_inline_formatting_after_an_unmatched_run(): void
    {
        $this->assertSame(
            '<p>About ~<strong>5 km</strong></p>',
            new Markdown(highlighter: null)->parse('About ~**5 km**')->html,
        );
    }
}
