<?php

namespace Tempest\Markdown;

use Tempest\Highlight\Highlighter;
use Tempest\Markdown\Rules\DivRule;
use Tempest\Markdown\Rules\FrontMatterRule;
use Tempest\Markdown\Rules\HeadingRule;
use Tempest\Markdown\Rules\HtmlCommentRule;
use Tempest\Markdown\Rules\HtmlRule;
use Tempest\Markdown\Rules\ListRule;
use Tempest\Markdown\Rules\NewLineRule;
use Tempest\Markdown\Rules\OrderedListRule;
use Tempest\Markdown\Rules\ParagraphRule;
use Tempest\Markdown\Rules\PreRule;
use Tempest\Markdown\Rules\QuoteRule;
use Tempest\Markdown\Rules\RawRule;
use Tempest\Markdown\Rules\TableRule;
use Tempest\Markdown\Rules\ThickRulerRule;
use Tempest\Markdown\Rules\ThinRulerRule;
use Tempest\Markdown\Tokens\FrontMatterToken;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

final class Parser
{
    public const string WHITESPACE = "\r\n\t\f ";
    public const string NEW_LINE = "\r\n";

    private(set) int $position = 0;
    private(set) int $length = 0;
    private(set) ?string $current;
    private(set) string $content;
    private(set) ?Token $lastToken = null;
    /** @var \Tempest\Markdown\Rule[] */
    private(set) array $rules = [];
    /** @var \Tempest\Markdown\Rule[] */
    private(set) array $defaultRules = [];
    /** @var \Tempest\Markdown\Rule[][] */
    private(set) array $perCharRules = [];

    /** @var \Tempest\Markdown\Parser[] */
    private array $cache = [];

    public function __construct(
        public ?Highlighter $highlighter = new Highlighter(),
        public ?ResponsiveImageFactory $imageFactory = null,
        array $rules = [
            new NewLineRule(),
            new RawRule(),
            new FrontMatterRule(),
            new HeadingRule(),
            new QuoteRule(),
            new PreRule(),
            new DivRule(),
            new ThinRulerRule(),
            new ThickRulerRule(),
            new ListRule(),
            new OrderedListRule(),
            new HtmlCommentRule(),
            new HtmlRule(),
            new TableRule(),
            new ParagraphRule(),
        ],
    ) {
        $this->setRules($rules);
    }

    public function forToken(Token $token, array $rules): self
    {
        if (isset($this->cache[$token::class])) {
            return $this->cache[$token::class];
        }

        $clone = clone $this;

        $clone->setRules($rules);

        $this->cache[$token::class] = $clone;

        return $clone;
    }

    public function withRules(Rule ...$rules): self
    {
        $clone = clone $this;

        return $clone->setRules($rules);
    }

    public function prependRules(Rule ...$rules): self
    {
        $clone = clone $this;

        return $clone->setRules([...$rules, ...$this->rules]);
    }

    public function appendRules(Rule ...$rules): self
    {
        $clone = clone $this;

        return $clone->setRules([...$this->rules, ...$rules]);
    }

    public function removeRules(string ...$rules): self
    {
        $currentRules = $this->rules;

        foreach ($currentRules as $key => $rule) {
            if (! in_array($rule::class, $rules, strict: true)) {
                continue;
            }

            unset($currentRules[$key]);
        }

        $clone = clone $this;

        $clone->setRules($currentRules);

        return $clone;
    }

    private function setRules(array $rules): self
    {
        /** @var \Tempest\Markdown\NeedsStopChars[] $needsStopChars */
        $needsStopChars = [];
        $providedStopChars = '';

        foreach ($rules as $rule) {
            if ($rule instanceof ProvidesStopChar) {
                $providedStopChars .= $rule->stopChar;
            }

            if ($rule instanceof NeedsStopChars) {
                $needsStopChars[] = $rule;
            }
        }

        foreach ($needsStopChars as $rule) {
            $rule->stopChars .= $providedStopChars;
        }

        $perCharRules = [];
        $defaultRules = [];

        foreach ($rules as $rule) {
            if ($rule instanceof ProvidesFirstChar) {
                foreach (str_split($rule->firstChar) as $char) {
                    $perCharRules[$char] ??= $defaultRules;
                    $perCharRules[$char][] = $rule;
                }
            } else {
                $charRules = [];

                foreach ($perCharRules as &$charRules) {
                    $charRules[] = $rule;
                }

                unset($charRules);

                $defaultRules[] = $rule;
            }
        }

        /** @var \Tempest\Markdown\Rule[] $rules */
        /** @var \Tempest\Markdown\Rule[] $defaultRules */
        /** @var \Tempest\Markdown\Rule[][] $perCharRules */
        $this->rules = $rules;
        $this->defaultRules = $defaultRules;
        $this->perCharRules = $perCharRules;

        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        $this->position = 0;
        $this->current = $this->content[$this->position] ?? null;
        $this->length = strlen($content);

        return $this;
    }

    public function lex(string $content): TokenCollection
    {
        $parser = clone $this;

        $parser->setContent($content);

        $tokens = [];

        while ($parser->current !== null) {
            foreach ($parser->perCharRules[$parser->current] ?? $parser->defaultRules as $rule) {
                if (! $rule->shouldParse($parser)) {
                    continue;
                }

                $token = $rule->parse($parser);

                if ($token instanceof Token) {
                    $tokens[] = $token;
                    $parser->lastToken = $token;
                }

                continue 2;
            }

            $parser->consume();
        }

        return new TokenCollection($tokens);
    }

    public function parse(string $content): ParsedMarkdown
    {
        $tokens = $this->lex($content);

        $html = '';

        $frontMatter = [];

        foreach ($tokens as $token) {
            $html .= $token->parse($this);

            if ($token instanceof FrontMatterToken) {
                $frontMatter = [...$frontMatter, ...$token->data];
            }
        }

        return new ParsedMarkdown($html, $frontMatter);
    }

    public function comesNext(string $search, ?int $length = null, int $offset = 0): bool
    {
        $length ??= strlen($search);

        if ($length === 1) {
            return ($this->content[$this->position + $offset] ?? null) === $search;
        }

        return substr_compare($this->content, $search, $this->position + $offset, $length) === 0;
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
