<?php

declare(strict_types=1);

namespace Tempest\Markdown\Tests;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Markdown\Parser;

final class FrontMatterTest extends TestCase
{
    private Parser $parser;

    #[Before]
    public function setupParser(): void
    {
        $this->parser = new Parser(highlighter: null);
    }

    #[Test]
    public function basic_yaml_extracted_and_html_omits_front_matter(): void
    {
        $input = <<<'MD'
        ---
        title: Hello
        foo: bar
        ---

        Bar
        MD;

        $parsed = $this->parser->parse($input);

        $this->assertSame('<p>Bar</p>', $parsed->html);
        $this->assertSame(['title' => 'Hello', 'foo' => 'bar'], $parsed->frontMatter);
    }

    #[Test]
    public function longer_delimiter_lines(): void
    {
        $input = <<<'MD'
        -----
        title: Hello
        foo: bar
        -----

        Bar
        MD;

        $parsed = $this->parser->parse($input);

        $this->assertSame('<p>Bar</p>', $parsed->html);
        $this->assertSame(['title' => 'Hello', 'foo' => 'bar'], $parsed->frontMatter);
    }

    #[Test]
    public function multiline_quoted_yaml_value(): void
    {
        $input = <<<'MD'
        ---
        title: Introduction
        description: "Tempest is a framework for PHP development, designed to get out of your way.
        Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework."
        ---

        Bar
        MD;

        $parsed = $this->parser->parse($input);

        $this->assertSame('<p>Bar</p>', $parsed->html);
        $this->assertSame([
            'title' => 'Introduction',
            'description' => 'Tempest is a framework for PHP development, designed to get out of your way. Its core philosophy is to help you focus on your application code, without being bothered hand-holding the framework.',
        ], $parsed->frontMatter);
    }
}
