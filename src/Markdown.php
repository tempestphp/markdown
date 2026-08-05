<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

final class Markdown
{
    private Parser $parser;

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
    }

    public function parse(string $content): ParsedMarkdown
    {
        return $this->parser->parse($content);
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
