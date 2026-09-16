<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
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
        $html =
            (string) new Parser(highlighter: null, rules: [new StrikethroughRule()])->parse(
                '~~strikethrough~~',
            );

        $this->assertSame('<s>strikethrough</s>', $html);
    }

    #[Test]
    public function test_lex_single_tilde(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new StrikethroughRule()])->parse(
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
    #[DataProvider('provideFlanking')]
    public function delimiters_follow_the_flanking_rules(
        string $markdown,
        string $expected,
    ): void {
        $this->assertSame(
            $expected,
            new Markdown(highlighter: null)->parse($markdown)->html,
        );
    }

    public static function provideFlanking(): iterable
    {
        yield 'one tilde' => ['~struck~', '<p><s>struck</s></p>'];
        yield 'two tildes' => ['~~struck~~', '<p><s>struck</s></p>'];
        yield 'inside a word' => ['a~struck~b', '<p>a<s>struck</s>b</p>'];

        // The opening run may not be followed by whitespace, and the closing
        // run may not be preceded by it.
        yield 'space after the opening run' => [
            '~ struck ~',
            '<p>~ struck ~</p>',
        ];
        yield 'space before the closing run' => [
            '~11km / ~750m',
            '<p>~11km / ~750m</p>',
        ];
        yield 'approximations in a list item' => [
            "- Distance : ~120 km\n- Dénivelé : ~6 700 m D+ / ~7 300 m D-",
            '<ul><li>Distance : ~120 km</li>'
                . '<li>Dénivelé : ~6 700 m D+ / ~7 300 m D-</li></ul>',
        ];

        yield 'approximations in a table cell' => [
            "| a |\n| - |\n| ~120 et ~700 |",
            '<table><thead><tr><th>a</th></tr></thead>'
                . '<tbody><tr><td>~120 et ~700</td></tr></tbody></table>',
        ];

        // Opening and closing runs have to be the same length.
        yield 'one tilde closed by two' => ['~struck~~', '<p>~struck~~</p>'];
        yield 'two tildes closed by one' => ['~~struck~', '<p>~~struck~</p>'];
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
