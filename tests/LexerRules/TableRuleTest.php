<?php

namespace Tempest\Markdown\Tests\LexerRules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Lexer;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\TableRule;
use Tempest\Markdown\Tokens\ParagraphToken;
use Tempest\Markdown\Tokens\TableRow;
use Tempest\Markdown\Tokens\TableToken;

class TableRuleTest extends TestCase
{
    #[Test]
    public function test_lex_header_only(): void
    {
        $tokens = new Lexer([new TableRule()])->lex("| A | B |\n| --- | --- |");

        $this->assertEquals(
            new TableToken([
                new TableRow(['A', 'B'], isHeader: true),
            ]),
            $tokens[0],
        );
    }

    #[Test]
    public function test_lex_full_table(): void
    {
        $token = new Lexer([new TableRule()])->lex("| A | B |\n| --- | --- |\n| 1 | 2 |")[0];

        $this->assertEquals(
            new TableToken([
                new TableRow(['A', 'B'], isHeader: true),
                new TableRow(['1', '2'], isHeader: false),
            ]),
            $token,
        );
    }

    #[Test]
    public function test_lex_multiple_data_rows(): void
    {
        $token = new Lexer([new TableRule()])->lex("| A | B |\n| --- | --- |\n| 1 | 2 |\n| 3 | 4 |")[0];

        $this->assertEquals(
            new TableToken([
                new TableRow(['A', 'B'], isHeader: true),
                new TableRow(['1', '2'], isHeader: false),
                new TableRow(['3', '4'], isHeader: false),
            ]),
            $token,
        );
    }

    #[Test]
    public function test_lex_separator_with_alignment(): void
    {
        $token = new Lexer([new TableRule()])->lex("| A | B | C |\n| :--- | :---: | ---: |\n| 1 | 2 | 3 |")[0];

        $this->assertEquals(
            new TableToken([
                new TableRow(['A', 'B', 'C'], isHeader: true),
                new TableRow(['1', '2', '3'], isHeader: false),
            ]),
            $token,
        );
    }

    #[Test]
    public function test_table_with_empty_cells(): void
    {
        $token = new Lexer([new TableRule()])->lex("| A | B | C |\n| :--- | :---: | ---: |\n| | | 3 |")[0];

        $this->assertEquals(
            new TableToken([
                new TableRow(['A', 'B', 'C'], isHeader: true),
                new TableRow(['', '', '3'], isHeader: false),
            ]),
            $token,
        );
    }

    #[Test]
    public function test_paragraphs_with_pipe_are_not_treated_as_tables(): void
    {
        $tokens = new Lexer([new TableRule(), new ParagraphRule()])->lex("| Hello |\nHello");

        $this->assertCount(2, $tokens);
        $this->assertInstanceOf(ParagraphToken::class, $tokens[0]);
        $this->assertInstanceOf(ParagraphToken::class, $tokens[1]);
    }
}
