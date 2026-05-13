<?php

namespace Tempest\Markdown;

use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\PreRule;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\LexerRules\ThickRulerRule;
use Tempest\Markdown\LexerRules\ThinRulerRule;

final class Lexer
{
    public const string WHITESPACE = "\r\n\t\f ";
    public const string NEW_LINE = "\r\n";

    private(set) int $position = 0;
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
            new HeadingRule(),
            new QuoteRule(),
            new PreRule(),
            new ThinRulerRule(),
            new ThickRulerRule(),
            new ParagraphRule(),
        ];
    }

    public function withRules(Rule ...$rules): self
    {
        return clone($this, [
            'rules' => $rules,
        ]);
    }

    public function lex(string $content): TokenCollection
    {
        $lexer = clone $this;

        $lexer->content = $content;
        $lexer->position = 0;
        $lexer->current = $lexer->content[$lexer->position] ?? null;

        $tokens = [];

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

    public function consumeWhile(string $continueWhile): string
    {
        $offset = strspn($this->content, $continueWhile, $this->position);

        return $this->consume($offset);
    }

    public function consumeIncluding(string $search): string
    {
        return $this->consumeUntil($search) . $this->consume(strlen($search));
    }
}
