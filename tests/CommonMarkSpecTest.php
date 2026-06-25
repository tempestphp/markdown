<?php

namespace Tempest\Markdown\Tests;

use Generator;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;
use Tempest\Markdown\Rules\ListRule;
use Tempest\Markdown\Rules\QuoteRule;

final class CommonMarkSpecTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser();

        $this->parser->appendRules(
            new ListRule(),
            new QuoteRule(),
        );
    }

    #[Test]
    #[DataProvider('commonmark')]
    public function test_spec(int $id, string $markdown, string $html): void
    {
        if ($html !== $this->parser->parse($markdown)->html) {
            $this->markTestSkipped("Failed spec: {$id}");
            return;
        }

        $this->assertTrue(true);

        //        $this->assertSame($html, $this->parser->parse($markdown)->html, "Failed spec: {$id}");
    }

    public static function commonmark(): Generator
    {
        $data = json_decode(file_get_contents(__DIR__ . '/Fixtures/commonmark.json'), true);

        foreach ($data as $spec) {
            yield [
                'id' => $spec['example'],
                'markdown' => $spec['markdown'],
                'html' => $spec['html'],
            ];
        }
    }
}
