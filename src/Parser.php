<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Tempest\Markdown\ParserRules\DivRule;
use Tempest\Markdown\ParserRules\FrontMatterRule;
use Tempest\Markdown\ParserRules\HeadingRule;
use Tempest\Markdown\ParserRules\HtmlRule;
use Tempest\Markdown\ParserRules\ListRule;
use Tempest\Markdown\ParserRules\NewLineRule;
use Tempest\Markdown\ParserRules\OrderedListRule;
use Tempest\Markdown\ParserRules\ParagraphRule;
use Tempest\Markdown\ParserRules\PreRule;
use Tempest\Markdown\ParserRules\QuoteRule;
use Tempest\Markdown\ParserRules\TableRule;
use Tempest\Markdown\ParserRules\ThickRulerRule;
use Tempest\Markdown\ParserRules\ThinRulerRule;

final class Parser
{
    public const string WHITESPACE = "\r\n\t\f ";
    public const string NEW_LINE = "\r\n";

    private(set) int $position = 0;
    private(set) int $length = 0;
    private(set) ?string $current;
    private(set) string $content;
    public array $frontMatter = [];
    /** @var \Tempest\Markdown\Rule[] */
    private array $rules;

    /** @param null|Rule[] $rules */
    public function __construct(
        ?array $rules = null,
        public ?Highlighter $highlighter = new Highlighter(),
    ) {
        $this->rules = $rules ?? [
            new NewLineRule(),
            new FrontMatterRule(),
            new HeadingRule(),
            new QuoteRule(),
            new PreRule(),
            new DivRule(),
            new ThinRulerRule(),
            new ThickRulerRule(),
            new ListRule(),
            new OrderedListRule(),
            new TableRule(),
            new HtmlRule(),
            new ParagraphRule(),
        ];
    }

    public function withRules(Rule ...$rules): self
    {
        return clone($this, [
            'rules' => $rules,
        ]);
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        $this->position = 0;
        $this->current = $this->content[$this->position] ?? null;
        $this->length = strlen($content);

        return $this;
    }

    public function parse(string $content): ParsedMarkdown
    {
        $parser = clone $this;

        $parser->setContent($content);

        /** @var \Tempest\Markdown\NeedsStopChars[] $needsStopChars */
        $needsStopChars = [];
        $providedStopChars = '';

        /** @var \Tempest\Markdown\NeedsRules[] $needsRules */
        $needsRules = [];

        foreach ($this->rules as $rule) {
            if ($rule instanceof ProvidesStopChar) {
                $providedStopChars .= $rule->stopChar;
            }

            if ($rule instanceof NeedsStopChars) {
                $needsStopChars[] = $rule;
            }

            if ($rule instanceof NeedsRules) {
                $needsRules[] = $rule;
            }
        }

        foreach ($needsStopChars as $rule) {
            $rule->stopChars .= $providedStopChars;
        }

        foreach ($needsRules as $rule) {
            $rule->otherRules = array_values(array_filter($this->rules, fn ($r) => $r !== $rule));
        }

        $html = '';

        while ($parser->current !== null) {
            foreach ($this->rules as $rule) {
                if (! $rule->shouldParse($parser)) {
                    continue;
                }

                $html .= $rule->parse($parser);

                continue 2;
            }

            $parser->consume();
        }

        return new ParsedMarkdown($html, $parser->frontMatter);
    }

    public function comesNext(string $search, ?int $length = null): bool
    {
        $length ??= strlen($search);

        if ($length === 1) {
            return ($this->content[$this->position] ?? null) === $search;
        }

        return substr_compare($this->content, $search, $this->position, $length) === 0;
    }

    public function consume(int $length = 1): string
    {
        if ($length === 0) {
            return '';
        }

        if ($length === 1) {
            $char = $this->content[$this->position++] ?? null;
            $this->current = $this->content[$this->position] ?? null;
            return $char ?? '';
        }

        $buffer = substr($this->content, $this->position, $length);
        $this->position += $length;
        $this->current = $this->content[$this->position] ?? null;

        return $buffer;
    }

    public function consumeUntil(string $stopAt): string
    {
        $offset = strcspn($this->content, $stopAt, $this->position);

        return $this->consume($offset);
    }

    public function consumeUntilString(string $stopAt): string
    {
        $pos = strpos($this->content, $stopAt, $this->position);

        if ($pos === false) {
            return $this->consume(strlen($this->content) - $this->position);
        }

        return $this->consume($pos - $this->position);
    }

    public function consumeWhile(string $continueWhile): string
    {
        $offset = strspn($this->content, $continueWhile, $this->position);

        return $this->consume($offset);
    }

    public function consumeIncluding(string $search): string
    {
        return $this->consumeUntil($search) . $this->consume(strlen($search));
    }

    public function lookaheadUntil(string ...$stopAt): array
    {
        $results = [];
        $position = $this->position;

        foreach ($stopAt as $stopAtChar) {
            $offset = strcspn($this->content, $stopAtChar, $position) + 1;

            if ($offset > $this->length) {
                break;
            }

            $substr = substr($this->content, $position, $offset);

            if ($substr === '') {
                break;
            }

            $results[] = $substr;
            $position += $offset;
        }

        return $results;
    }

    public function withPosition(int $originalPosition): self
    {
        return clone($this, [
            'position' => $originalPosition,
        ]);
    }
}
