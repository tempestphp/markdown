<?php

namespace Tempest\Markdown\Tests\Tokens;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Tokens\TableRow;
use Tempest\Markdown\Tokens\TableToken;

class TableTokenTest extends TestCase
{
    #[Test]
    public function test_parse(): void
    {
        $token = new TableToken([
            new TableRow(['Name', 'Age'], isHeader: true),
            new TableRow(['Alice', '30'], isHeader: false),
        ]);

        $this->assertEquals(
            '<table><thead><tr><th>Name</th><th>Age</th></tr></thead><tbody><tr><td>Alice</td><td>30</td></tr></tbody></table>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_header_only(): void
    {
        $token = new TableToken([
            new TableRow(['A', 'B'], isHeader: true),
        ]);

        $this->assertEquals(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead></table>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_with_inline_formatting(): void
    {
        $token = new TableToken([
            new TableRow(['Name', 'Notes'], isHeader: true),
            new TableRow(['**Alice**', '`code`'], isHeader: false),
        ]);

        $this->assertEquals(
            '<table><thead><tr><th>Name</th><th>Notes</th></tr></thead><tbody><tr><td><strong>Alice</strong></td><td><code class="language-txt">code</code></td></tr></tbody></table>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_with_image(): void
    {
        $token = new TableToken([
            new TableRow(['![image](#)', '`code`'], isHeader: false),
        ]);

        $this->assertEquals(
            '<table><tbody><tr><td><img src="#" alt="image"></td><td><code class="language-txt">code</code></td></tr></tbody></table>',
            $token->parse(new Parser()),
        );
    }

    #[Test]
    public function test_parse_multiple_rows(): void
    {
        $token = new TableToken([
            new TableRow(['A', 'B'], isHeader: true),
            new TableRow(['1', '2'], isHeader: false),
            new TableRow(['3', '4'], isHeader: false),
        ]);

        $this->assertEquals(
            '<table><thead><tr><th>A</th><th>B</th></tr></thead><tbody><tr><td>1</td><td>2</td></tr><tr><td>3</td><td>4</td></tr></tbody></table>',
            $token->parse(new Parser()),
        );
    }
}
