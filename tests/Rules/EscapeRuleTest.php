<?php

namespace Tempest\Markdown\Tests\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Tests\ParserTestCase;

final class EscapeRuleTest extends ParserTestCase
{
    #[Test]
    #[DataProvider('provideEscapes')]
    public function escaped_punctuation_is_literal(
        string $markdown,
        string $expected,
    ): void {
        $this->assertSame(
            $expected,
            new Markdown(highlighter: null)->parse($markdown)->html,
        );
    }

    public static function provideEscapes(): iterable
    {
        yield 'asterisks' => [
            'Hôtels 3\*\*\* et 4\*\*\*\*',
            '<p>Hôtels 3*** et 4****</p>',
        ];
        yield 'emphasis delimiters' => [
            '\*not emphasis\*',
            '<p>*not emphasis*</p>',
        ];
        yield 'underscores' => ['\_x\_', '<p>_x_</p>'];
        yield 'link brackets' => ['\[x\](/y)', '<p>[x](/y)</p>'];
        yield 'ordered list marker' => ['1\. Step', '<p>1. Step</p>'];
        yield 'heading marker' => ['\# Title', '<p># Title</p>'];
        yield 'backslash' => ['a\\\\b', '<p>a\b</p>'];
        yield 'markup' => ['a\<b', '<p>a&lt;b</p>'];
        yield 'in a list item' => ['- \* star', '<ul><li>* star</li></ul>'];
        yield 'in a table cell' => [
            "| a |\n| - |\n| 3\*\* |",
            '<table><thead><tr><th>a</th></tr></thead>'
                . '<tbody><tr><td>3**</td></tr></tbody></table>',
        ];

        // Only ASCII punctuation can be escaped, and code spans keep their
        // backslashes.
        yield 'before a letter' => ['a\qb', '<p>a\qb</p>'];
        yield 'inside code' => ['`a\*b`', '<p><code>a\*b</code></p>'];

        // A backslash before a line ending is a hard line break, an escaped
        // one is not.
        yield 'hard line break' => ["a\\\nb", "<p>a<br />\nb</p>"];
        yield 'escaped backslash before a line ending' => [
            "a\\\\\nb",
            "<p>a&#92;\nb</p>",
        ];
    }
}
