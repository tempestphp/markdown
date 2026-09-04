<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

final class Markdown
{
    private Parser $parser;

    private MultiMarkdownSplitter $splitter;

    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
        private ?ResponsiveImageFactory $imageFactory = null,
        public int $maxNestingDepth = Parser::DEFAULT_MAX_NESTING_DEPTH,
    ) {
        $this->parser = new Parser(
            $this->highlighter,
            $this->imageFactory,
            $this->maxNestingDepth,
        );

        $this->splitter = new MultiMarkdownSplitter();
    }

    public function parse(string $content, ?string $name = null): ParsedMarkdown
    {
        $parsed = $this->parser->parse($content);

        return $name === null ? $parsed : new ParsedMarkdown($parsed->html, $parsed->frontmatter, $name);
    }

    public function parseMany(string $content, ?string $baseName = null, string $keyword = 'next'): ParsedMarkdownCollection
    {
        $chunks = $this->splitter->split($content, $baseName, $keyword);

        return new ParsedMarkdownCollection(array_map(
            fn (array $chunk): ParsedMarkdown => $this->parse($chunk['content'], $chunk['name']),
            $chunks,
        ));
    }

    public function withRules(Rule ...$rules): self
    {
        $this->parser = $this->parser->withRules(...$rules);

        return $this;
    }

    public function prependRules(Rule ...$rules): self
    {
        $this->parser = $this->parser->prependRules(...$rules);

        return $this;
    }

    public function appendRules(Rule ...$rules): self
    {
        $this->parser = $this->parser->appendRules(...$rules);

        return $this;
    }

    public function removeRules(string ...$rules): self
    {
        $this->parser = $this->parser->removeRules(...$rules);

        return $this;
    }
}
