<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Rules\TableRule;
use Tempest\Markdown\Tests\ParserTestCase;

class TableRuleTest extends ParserTestCase
{
    #[Test]
    public function test_lex_header_only(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B |\n| --- | --- |",
            );

        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead></table>',
            $html,
        );
    }

    #[Test]
    public function test_lex_full_table(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B |\n| --- | --- |\n| 1 | 2 |",
            );

        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_lex_multiple_data_rows(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B |\n| --- | --- |\n| 1 | 2 |\n| 3 | 4 |",
            );

        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_lex_separator_with_alignment(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B | C |\n| :--- | :---: | ---: |\n| 1 | 2 | 3 |",
            );

        $this->assertSame(
            '<table><thead><tr><th align="left">A</th><th align="center">B</th><th align="right">C</th></tr></thead><tbody><tr><td align="left">1</td><td align="center">2</td><td align="right">3</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_columns_without_alignment(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B |\n| --- | ---: |\n| 1 | 2 |",
            );

        $this->assertSame(
            '<table><thead><tr><th>A</th><th align="right">B</th></tr></thead><tbody><tr><td>1</td><td align="right">2</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_alignment_without_spaces_or_trailing_pipe(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B |\n|:-:|-:\n| 1 | 2 |",
            );

        $this->assertSame(
            '<table><thead><tr><th align="center">A</th><th align="right">B</th></tr></thead><tbody><tr><td align="center">1</td><td align="right">2</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_only_the_row_after_the_header_is_a_separator(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A |\n| :--- |\n| 1 |\n| --- |\n| 2 |",
            );

        $this->assertSame(
            '<table><thead><tr><th align="left">A</th></tr></thead><tbody><tr><td align="left">1</td></tr><tr><td align="left">---</td></tr><tr><td align="left">2</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_alignment_applies_to_every_data_row(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A |\n| :---: |\n| 1 |\n| 2 |",
            );

        $this->assertSame(
            '<table><thead><tr><th align="center">A</th></tr></thead><tbody><tr><td align="center">1</td></tr><tr><td align="center">2</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_table_with_empty_cells(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B | C |\n| :--- | :---: | ---: |\n| | | 3 |",
            );

        $this->assertSame(
            '<table><thead><tr><th align="left">A</th><th align="center">B</th><th align="right">C</th></tr></thead><tbody><tr><td align="left"></td><td align="center"></td><td align="right">3</td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_table_with_all_empty_cells(): void
    {
        $html =
            (string) new Parser(highlighter: null, rules: [new TableRule()])->parse(
                "| A | B |\n| --- | --- |\n| | |",
            );

        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td></td><td></td></tr></tbody></table>',
            $html,
        );
    }

    #[Test]
    public function test_paragraphs_with_pipe_are_not_treated_as_tables(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new TableRule(),
            new ParagraphRule(),
        ])->parse("| Hello |\nHello");

        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringContainsString('<p>', $html);
    }

    #[Test]
    public function test_separator_cells_must_contain_hyphens(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [
            new TableRule(),
            new ParagraphRule(),
        ])->parse("| not | table |\n| : | : |");

        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringContainsString('<p>', $html);
    }
}
