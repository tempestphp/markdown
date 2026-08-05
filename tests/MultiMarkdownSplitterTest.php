<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\MultiMarkdownSplitter;

final class MultiMarkdownSplitterTest extends TestCase
{
    #[Test]
    public function test_a_dangling_trailing_auto_marker_with_no_content_after_it_is_dropped(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("<!-- next: X -->\nA\n\n<!-- next: Y -->\nB\n<!-- next -->");

        $this->assertCount(2, $chunks);
        $this->assertSame('X', $chunks[0]['name']);
        $this->assertSame('A', $chunks[0]['content']);
        $this->assertSame('Y', $chunks[1]['name']);
        $this->assertSame('B', $chunks[1]['content']);
    }

    #[Test]
    public function test_a_trailing_explicitly_named_marker_with_no_content_after_it_is_kept(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("<!-- next: X -->\nA\n<!-- next: Z -->");

        $this->assertCount(2, $chunks);
        $this->assertSame('Z', $chunks[1]['name']);
        $this->assertSame('', $chunks[1]['content']);
    }

    #[Test]
    public function test_content_that_is_only_a_dangling_auto_marker_produces_no_parts(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split('<!-- next -->');

        $this->assertSame([], $chunks);
    }

    #[Test]
    public function test_once_any_marker_is_present_no_part_is_ever_left_nameless(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("X\n<!-- next -->Y");

        $this->assertCount(2, $chunks);
        $this->assertSame('chunk-1', $chunks[0]['name']);
        $this->assertSame('X', $chunks[0]['content']);
        $this->assertSame('chunk-2', $chunks[1]['name']);
        $this->assertSame('Y', $chunks[1]['content']);
    }

    #[Test]
    public function test_content_without_markers_is_a_single_unnamed_document(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split('Hello world');

        $this->assertCount(1, $chunks);
        $this->assertNull($chunks[0]['name']);
        $this->assertSame('Hello world', $chunks[0]['content']);
    }

    #[Test]
    public function test_splits_on_next_colon_named_markers(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split(<<<MD
        First document

        <!-- next: services/db-primary.md -->
        Second document
        MD);

        $this->assertCount(2, $chunks);
        $this->assertSame('chunk-1', $chunks[0]['name']);
        $this->assertSame('First document', $chunks[0]['content']);
        $this->assertSame('services/db-primary.md', $chunks[1]['name']);
        $this->assertSame('Second document', $chunks[1]['content']);
    }

    #[Test]
    public function test_marker_at_the_very_start_produces_no_leading_unnamed_document(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split(<<<MD
        <!-- next: readme.md -->
        Only document
        MD);

        $this->assertCount(1, $chunks);
        $this->assertSame('readme.md', $chunks[0]['name']);
        $this->assertSame('Only document', $chunks[0]['content']);
    }

    #[Test]
    public function test_bare_next_marker_auto_numbers_from_base_name(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split(<<<MD
        <!-- next -->
        A

        <!-- next -->
        B
        MD, baseName: 'readme.md');

        $this->assertSame('readme.md-1', $chunks[0]['name']);
        $this->assertSame('readme.md-2', $chunks[1]['name']);
    }

    #[Test]
    public function test_base_name_is_never_treated_as_a_path_with_an_extension(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("<!-- next -->\nA", baseName: 'v1.2');

        $this->assertSame('v1.2-1', $chunks[0]['name']);
    }

    #[Test]
    public function test_auto_numbering_counts_every_emitted_chunk_not_just_auto_named_ones(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("X\n<!-- next -->Y\nXYZ\n<!-- next: My/NoDoc.txt -->\n<!-- next -->Z");

        $this->assertSame(
            ['chunk-1', 'chunk-2', 'My/NoDoc.txt', 'chunk-4'],
            array_column($chunks, 'name'),
        );
    }

    #[Test]
    public function test_renaming_one_chunk_does_not_shift_another_chunks_auto_number(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $before = $splitter->split("<!-- next -->\nA\n<!-- next -->\nB\n<!-- next -->\nC");
        $after = $splitter->split("<!-- next -->\nA\n<!-- next: foo -->\nB\n<!-- next -->\nC");

        $this->assertSame('chunk-3', $before[2]['name']);
        $this->assertSame('chunk-3', $after[2]['name']);
    }

    #[Test]
    public function test_next_colon_wildcard_is_equivalent_to_bare_next(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $bare = $splitter->split("<!-- next -->\nA", baseName: 'readme.md');
        $wildcard = $splitter->split("<!-- next: * -->\nA", baseName: 'readme.md');

        $this->assertSame($bare, $wildcard);
    }

    #[Test]
    public function test_next_marker_falls_back_without_base_name(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("<!-- next -->\nA");

        $this->assertSame('chunk-1', $chunks[0]['name']);
    }

    #[Test]
    public function test_arbitrary_blank_lines_between_marker_and_content_are_irrelevant(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $tight = $splitter->split("<!-- next: a.md -->\n---\ntitle: A\n---\nBody");
        $loose = $splitter->split("<!-- next: a.md -->\n\n\n---\ntitle: A\n---\nBody");

        $this->assertSame($tight[0]['content'], $loose[0]['content']);
        $this->assertSame("---\ntitle: A\n---\nBody", $tight[0]['content']);
    }

    #[Test]
    public function test_incidental_html_comments_are_never_treated_as_markers(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split(<<<MD
        Some text.

        <!-- TODO: revisit this paragraph -->
        <!-- note: next steps go here -->

        More text.
        MD);

        $this->assertCount(1, $chunks);
        $this->assertStringContainsString('<!-- TODO: revisit this paragraph -->', $chunks[0]['content']);
        $this->assertStringContainsString('<!-- note: next steps go here -->', $chunks[0]['content']);
    }

    #[Test]
    public function test_indented_marker_is_still_recognized(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("  <!-- next -->\nBody", baseName: 'readme.md');

        $this->assertSame('readme.md-1', $chunks[0]['name']);
        $this->assertSame('Body', $chunks[0]['content']);
    }

    #[Test]
    public function test_keyword_is_overridable(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split(<<<MD
        <!-- nextDoc -->
        A

        <!-- nextDoc: reports/q1.md -->
        B
        MD, keyword: 'nextDoc');

        $this->assertSame('chunk-1', $chunks[0]['name']);
        $this->assertSame('reports/q1.md', $chunks[1]['name']);
    }

    #[Test]
    public function test_default_keyword_is_ignored_once_overridden(): void
    {
        $splitter = new MultiMarkdownSplitter();

        $chunks = $splitter->split("<!-- next -->\nA", keyword: 'nextDoc');

        $this->assertCount(1, $chunks);
        $this->assertNull($chunks[0]['name']);
        $this->assertStringContainsString('<!-- next -->', $chunks[0]['content']);
    }
}
