<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\TableRule;
use Tempest\Markdown\Parser;

class TableRuleTest extends TestCase
{
    #[Test]
    public function test_lex_header_only(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule()])->parse("| A | B |\n| --- | --- |");

        $this->assertSame('<table><thead><tr><th>A</th><th>B</th></tr></thead></table>', $html);
    }

    #[Test]
    public function test_lex_full_table(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule()])->parse("| A | B |\n| --- | --- |\n| 1 | 2 |");

        $this->assertSame('<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>', $html);
    }

    #[Test]
    public function test_lex_multiple_data_rows(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule()])->parse("| A | B |\n| --- | --- |\n| 1 | 2 |\n| 3 | 4 |");

        $this->assertSame('<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody></table>', $html);
    }

    #[Test]
    public function test_lex_separator_with_alignment(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule()])->parse("| A | B | C |\n| :--- | :---: | ---: |\n| 1 | 2 | 3 |");

        $this->assertSame('<table><thead><tr><th>A</th><th>B</th><th>C</th></tr></thead><tbody><tr><td>1</td><td>2</td><td>3</td></tr></tbody></table>', $html);
    }

    #[Test]
    public function test_table_with_empty_cells(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule()])->parse("| A | B | C |\n| :--- | :---: | ---: |\n| | | 3 |");

        $this->assertSame('<table><thead><tr><th>A</th><th>B</th><th>C</th></tr></thead><tbody><tr><td></td><td></td><td>3</td></tr></tbody></table>', $html);
    }

    #[Test]
    public function test_table_with_all_empty_cells(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule()])->parse("| A | B |\n| --- | --- |\n| | |");

        $this->assertSame('<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td></td><td></td></tr></tbody></table>', $html);
    }

    #[Test]
    public function test_paragraphs_with_pipe_are_not_treated_as_tables(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule(), new ParagraphRule()])->parse("| Hello |\nHello");

        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringContainsString('<p>', $html);
    }

    #[Test]
    public function test_separator_cells_must_contain_hyphens(): void
    {
        $html = (string) new Parser(highlighter: null, rules: [new TableRule(), new ParagraphRule()])->parse("| not | table |\n| : | : |");

        $this->assertStringNotContainsString('<table>', $html);
        $this->assertStringContainsString('<p>', $html);
    }
}
