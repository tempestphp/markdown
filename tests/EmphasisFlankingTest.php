<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Markdown;

final class EmphasisFlankingTest extends ParserTestCase
{
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
        yield 'italic' => ['*a*', '<p><em>a</em></p>'];
        yield 'bold' => ['**a**', '<p><strong>a</strong></p>'];
        yield 'bold and italic' => [
            '***a***',
            '<p><strong><em>a</em></strong></p>',
        ];
        yield 'underscore italic' => ['_a_', '<p><em>a</em></p>'];
        yield 'underscore bold' => ['__a__', '<p><strong>a</strong></p>'];
        yield 'inside a word' => ['foo*bar*', '<p>foo<em>bar</em></p>'];

        // An opening delimiter may not be followed by whitespace, and a
        // closing one may not be preceded by it.
        yield 'spaced asterisks' => ['2 * 3 * 4', '<p>2 * 3 * 4</p>'];
        yield 'spaced double asterisks' => ['a ** b **', '<p>a ** b **</p>'];
        yield 'spaced triple asterisks' => [
            'a *** b ***',
            '<p>a *** b ***</p>',
        ];
        yield 'spaced underscores' => ['x _ y _', '<p>x _ y _</p>'];
        yield 'space before the closing asterisk' => ['*a *', '<p>*a *</p>'];
        yield 'space before the closing double asterisk' => [
            '**a **',
            '<p>**a **</p>',
        ];
        yield 'space after the opening double asterisk' => [
            '** a**',
            '<p>** a**</p>',
        ];
        yield 'space before the closing double underscore' => [
            '__a __',
            '<p>__a __</p>',
        ];
        yield 'hotel ratings' => [
            'Hôtels 3*** et 4****',
            '<p>Hôtels 3*** et 4****</p>',
        ];
        yield 'literal run after emphasis' => [
            '*a* * b *',
            '<p><em>a</em> * b *</p>',
        ];
    }
}
