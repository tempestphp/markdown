<?php

namespace Tempest\Markdown;

use Tempest\Markdown\LexerRules\DivRule;
use Tempest\Markdown\LexerRules\FrontMatterRule;
use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\LexerRules\HtmlRule;
use Tempest\Markdown\LexerRules\ListRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\OrderedListRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\PreRule;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\LexerRules\TableRule;
use Tempest\Markdown\LexerRules\ThickRulerRule;
use Tempest\Markdown\LexerRules\ThinRulerRule;

final class Lexer
{
    public const string WHITESPACE = "\r\n\t\f ";
    public const string NEW_LINE = "\r\n";

    private(set) int $position = 0;
    private(set) int $length = 0;
    private(set) ?string $current;
    private(set) string $content;
    /** @var \Tempest\Markdown\Rule[] */
    private array $rules;
    private(set) ?Token $lastToken = null;

    /** @param null|Rule[] $rules */
    public function __construct(?array $rules = null)
    {
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

    public function prependRules(Rule ...$rules): self
    {
        return clone($this, [
            'rules' => [...$rules, ...$this->rules],
        ]);
    }

    public function appendRules(Rule ...$rules): self
    {
        return clone($this, [
            'rules' => [...$this->rules, ...$rules],
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

    public function lex(string $content): TokenCollection
    {
        $lexer = clone $this;

        $lexer->setContent($content);

        $tokens = [];

        /** @var \Tempest\Markdown\NeedsStopChars[] $needsStopChars */
        $needsStopChars = [];
        $providedStopChars = '';

        foreach ($this->rules as $rule) {
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

        while ($lexer->current !== null) {
            foreach ($this->rules as $rule) {
                if (! $rule->shouldLex($lexer)) {
                    continue;
                }

                $token = $rule->lex($lexer);

                if ($token instanceof Token) {
                    $tokens[] = $token;
                    $lexer->lastToken = $token;
                }

                continue 2;
            }

            $lexer->consume();
        }

        return new TokenCollection($tokens);
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
