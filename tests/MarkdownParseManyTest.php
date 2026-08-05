<?php

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Markdown\Markdown;

final class MarkdownParseManyTest extends ParserTestCase
{
    private Markdown $markdown;

    #[Before]
    public function setupMarkdown(): void
    {
        $this->markdown = new Markdown();
    }

    #[Test]
    public function test_parse_many_without_markers_behaves_like_parse(): void
    {
        $chunks = $this->markdown->parseMany('**Hello**');

        $this->assertCount(1, $chunks);
        $this->assertNull($chunks[0]->name);
        $this->assertSame('<p><strong>Hello</strong></p>', $chunks[0]->html);
    }

    #[Test]
    public function test_parse_many_gives_each_document_its_own_frontmatter(): void
    {
        $chunks = $this->markdown->parseMany(<<<MD
        ---
        name: db-primary
        ---
        Primary database.

        <!-- next: hosts/web-1.md -->
        ---
        name: web-1
        ---
        Web host.
        MD);

        $this->assertCount(2, $chunks);

        $this->assertSame('chunk-1', $chunks[0]->name);
        $this->assertSame('db-primary', $chunks[0]->frontmatter['name']);
        $this->assertStringContainsString('Primary database', $chunks[0]->html);

        $this->assertSame('hosts/web-1.md', $chunks[1]->name);
        $this->assertSame('web-1', $chunks[1]->frontmatter['name']);
        $this->assertStringContainsString('Web host', $chunks[1]->html);
    }

    #[Test]
    public function test_marker_at_the_start_does_not_break_frontmatter_parsing(): void
    {
        $chunks = $this->markdown->parseMany(<<<MD
        <!-- next: readme.md -->
        ---
        title: A
        ---
        Body
        MD);

        $this->assertCount(1, $chunks);
        $this->assertSame('readme.md', $chunks[0]->name);
        $this->assertSame('A', $chunks[0]->frontmatter['title']);
    }

    #[Test]
    public function test_marker_at_the_start_tolerates_arbitrary_blank_lines_before_frontmatter(): void
    {
        $tight = $this->markdown->parseMany("<!-- next: a.md -->\n---\ntitle: A\n---\nBody");
        $loose = $this->markdown->parseMany("<!-- next: a.md -->\n\n\n---\ntitle: A\n---\nBody");

        $this->assertSame($tight[0]->frontmatter, $loose[0]->frontmatter);
        $this->assertSame($tight[0]->html, $loose[0]->html);
    }

    #[Test]
    public function test_a_document_without_frontmatter_still_works(): void
    {
        $chunks = $this->markdown->parseMany(<<<MD
        <!-- next -->
        Just prose, no frontmatter here.
        MD, baseName: 'notes.md');

        $this->assertSame('notes.md-1', $chunks[0]->name);
        $this->assertSame([], $chunks[0]->frontmatter);
    }

    #[Test]
    public function test_the_name_after_the_marker_can_be_a_plain_identifier_instead_of_a_path(): void
    {
        $chunks = $this->markdown->parseMany(<<<MD
        <!-- next: db-primary -->
        ---
        status: up
        ---
        Primary database.
        MD);

        $this->assertSame('db-primary', $chunks[0]->name);
        $this->assertSame('up', $chunks[0]->frontmatter['status']);
    }

    #[Test]
    public function test_known_limitation_marker_inside_a_code_fence_is_still_split(): void
    {
        $chunks = $this->markdown->parseMany(<<<MD
        Example of the marker syntax:

        ```md
        <!-- next -->
        ```

        More text.
        MD);

        $this->assertGreaterThan(1, count($chunks));
    }

    #[Test]
    public function test_the_infrastructure_fixture_parses_into_a_consistent_topology(): void
    {
        $content = file_get_contents(__DIR__ . '/Fixtures/infrastructure.md');
        $chunks = $this->markdown->parseMany($content);

        $byName = [];

        foreach ($chunks as $chunk) {
            $byName[$chunk->name] = $chunk->frontmatter;
        }

        $this->assertCount(5, $chunks);
        $this->assertSame(['web-1', 'db-1', 'web-service', 'db-service', 'storefront'], array_keys($byName));

        foreach ($byName as $frontmatter) {
            foreach (['runs_on', 'depends_on', 'composed_of'] as $relation) {
                foreach ($frontmatter[$relation] ?? [] as $reference) {
                    $this->assertArrayHasKey($reference, $byName);
                }
            }
        }

        $this->assertSame(['web-1'], $byName['web-service']['runs_on']);
        $this->assertSame(['web-service', 'db-service'], $byName['storefront']['composed_of']);
    }
}
