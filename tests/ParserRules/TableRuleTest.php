<?php

namespace Tempest\Markdown\Tests\ParserRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\ParserRules\ParagraphRule;
use Tempest\Markdown\ParserRules\TableRule;

class TableRuleTest extends TestCase
{
    #[Test]
    public function test_lex_header_only(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead></table>',
            (string) new Parser([new TableRule()])->parse("| A | B |\n| --- | --- |"),
        );
    }

    #[Test]
    public function test_lex_full_table(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>',
            (string) new Parser([new TableRule()])->parse("| A | B |\n| --- | --- |\n| 1 | 2 |"),
        );
    }

    #[Test]
    public function test_lex_multiple_data_rows(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody></table>',
            (string) new Parser([new TableRule()])->parse("| A | B |\n| --- | --- |\n| 1 | 2 |\n| 3 | 4 |"),
        );
    }

    #[Test]
    public function test_lex_separator_with_alignment(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th><th>C</th></tr></thead><tbody><tr><td>1</td><td>2</td><td>3</td></tr></tbody></table>',
            (string) new Parser([new TableRule()])->parse("| A | B | C |\n| :--- | :---: | ---: |\n| 1 | 2 | 3 |"),
        );
    }

    #[Test]
    public function test_table_with_empty_cells(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th><th>C</th></tr></thead><tbody><tr><td></td><td></td><td>3</td></tr></tbody></table>',
            (string) new Parser([new TableRule()])->parse("| A | B | C |\n| :--- | :---: | ---: |\n| | | 3 |"),
        );
    }

    #[Test]
    public function test_table_with_all_empty_cells(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td></td><td></td></tr></tbody></table>',
            (string) new Parser([new TableRule()])->parse("| A | B |\n| --- | --- |\n| | |"),
        );
    }

    #[Test]
    public function test_paragraphs_with_pipe_are_not_treated_as_tables(): void
    {
        $result = new Parser([new TableRule(), new ParagraphRule()])->parse("| Hello |\nHello");

        $this->assertSame('<p>| Hello |' . "\n" . '</p><p>Hello</p>', $result->html);
    }

    #[Test]
    public function test_separator_cells_must_contain_hyphens(): void
    {
        $result = new Parser([new TableRule(), new ParagraphRule()])->parse("| not | table |\n| : | : |");

        $this->assertSame('<p>| not | table |' . "\n" . '</p><p>| : | : |</p>', $result->html);
    }
}
