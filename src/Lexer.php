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
    private(set) int $line = 1;
    private(set) ?string $current;
    private(set) string $content;
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
        $lexer->line = 1;
        $lexer->current = $lexer->content[$lexer->position] ?? null;

        $tokens = [];

        while ($lexer->current !== null) {
            foreach ($this->rules as $rule) {
                if (! $rule->shouldLex($lexer)) {
                    continue;
                }

                $token = $rule->lex($lexer);

                if ($token) {
                    $tokens[] = $token;
                    $lexer->lastToken = $token;
                }

                continue 2;
            }

            $lexer->consume();
        }

        return new TokenCollection($tokens);
    }

    public function comesNext(string $search): bool
    {
        return $this->seek(strlen($search)) === $search;
    }

    public function seek(int $length = 1, int $offset = 0): ?string
    {
        $seek = substr($this->content, $this->position + $offset, $length);

        if ($seek === '') {
            return null;
        }

        return $seek;
    }

    public function seekIgnoringWhitespace(int $length = 1): ?string
    {
        $offset = strspn($this->content, self::WHITESPACE, $this->position);

        return $this->seek(length: $length, offset: $offset);
    }

    public function consume(int $length = 1): string
    {
        $buffer = substr($this->content, $this->position, $length);
        $this->position += $length;
        $this->line += substr_count($buffer, "\n");
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
