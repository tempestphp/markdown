<?php

namespace Tempest\Markdown\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\ParsedMarkdown;
use Tempest\Markdown\ParsedMarkdownCollection;

final class ParsedMarkdownCollectionTest extends TestCase
{
    #[Test]
    public function test_can_be_constructed_directly_from_an_array(): void
    {
        $collection = new ParsedMarkdownCollection([
            new ParsedMarkdown('<p>A</p>', [], 'a'),
            new ParsedMarkdown('<p>B</p>', [], 'b'),
        ]);

        $this->assertCount(2, $collection);
        $this->assertSame('a', $collection[0]->name);
        $this->assertSame('<p>B</p>', $collection['b']->html);
    }

    #[Test]
    public function test_parse_many_returns_a_collection_directly(): void
    {
        $markdown = new Markdown();
        $collection = $markdown->parseMany(<<<MD
        ---
        title: Pancakes
        ---
        Mix flour, eggs, and milk.

        <!-- next: recipe-2 -->
        ---
        title: Waffles
        ---
        Mix flour, eggs, and butter.
        MD);

        $this->assertInstanceOf(ParsedMarkdownCollection::class, $collection);
        $this->assertSame('Pancakes', $collection[0]->frontmatter['title']);
        $this->assertSame('Waffles', $collection['recipe-2']->frontmatter['title']);
    }

    #[Test]
    public function test_is_countable(): void
    {
        $markdown = new Markdown();
        $collection = $markdown->parseMany("<!-- next -->\nA\n<!-- next -->\nB");

        $this->assertCount(2, $collection);
    }

    #[Test]
    public function test_is_iterable(): void
    {
        $markdown = new Markdown();
        $collection = $markdown->parseMany("<!-- next: a -->\nA\n<!-- next: b -->\nB");

        $names = [];

        foreach ($collection as $chunk) {
            $names[] = $chunk->name;
        }

        $this->assertSame(['a', 'b'], $names);
    }

    #[Test]
    public function test_unnamed_chunks_are_only_reachable_by_index(): void
    {
        $markdown = new Markdown();
        $collection = $markdown->parseMany('Just prose, no markers at all.');

        $this->assertNull($collection[0]->name);
        $this->assertFalse(isset($collection['anything']));
    }

    #[Test]
    public function test_unknown_offset_returns_null(): void
    {
        $markdown = new Markdown();
        $collection = $markdown->parseMany("<!-- next: a -->\nA");

        $this->assertNull($collection['does-not-exist']);
        $this->assertNull($collection[99]);
    }

    #[Test]
    public function test_setting_an_explicit_offset_is_rejected(): void
    {
        $collection = new ParsedMarkdownCollection();

        $this->expectException(InvalidArgumentException::class);

        $collection['some-name'] = new ParsedMarkdown('<p>A</p>', [], 'a');
    }

    #[Test]
    public function test_appending_via_empty_brackets_works(): void
    {
        $collection = new ParsedMarkdownCollection();
        $collection[] = new ParsedMarkdown('<p>A</p>', [], 'a');

        $this->assertCount(1, $collection);
        $this->assertSame('a', $collection['a']->name);
    }
}
