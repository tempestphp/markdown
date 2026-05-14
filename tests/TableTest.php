<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class TableTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function header_only(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead></table>',
            $this->parser->parse("| A | B |\n| --- | --- |")->html,
        );
    }

    #[Test]
    public function full_table(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr></tbody></table>',
            $this->parser->parse("| A | B |\n| --- | --- |\n| 1 | 2 |")->html,
        );
    }

    #[Test]
    public function multiple_data_rows(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody></table>',
            $this->parser->parse("| A | B |\n| --- | --- |\n| 1 | 2 |\n| 3 | 4 |")->html,
        );
    }

    #[Test]
    public function separator_with_alignment_is_filtered(): void
    {
        // Separator rows with leading/trailing `:` (alignment markers) are still detected
        // and not emitted as data rows.
        $this->assertSame(
            '<table><thead><tr><th>A</th><th>B</th><th>C</th></tr></thead><tbody><tr><td>1</td><td>2</td><td>3</td></tr></tbody></table>',
            $this->parser->parse("| A | B | C |\n| :--- | :---: | ---: |\n| 1 | 2 | 3 |")->html,
        );
    }

    #[Test]
    public function inline_formatting_in_cells(): void
    {
        $this->assertSame(
            '<table><thead><tr><th>Name</th><th>Notes</th></tr></thead><tbody><tr><td><strong>Alice</strong></td><td><code>code</code></td></tr></tbody></table>',
            $this->parser->parse("| Name | Notes |\n| --- | --- |\n| **Alice** | `code` |")->html,
        );
    }
}
