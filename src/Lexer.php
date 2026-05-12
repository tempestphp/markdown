<?php

namespace Tempest\Markdown;

use Tempest\Markdown\LexerRules\HeadingRule;
use Tempest\Markdown\LexerRules\NewLineRule;
use Tempest\Markdown\LexerRules\ParagraphRule;
use Tempest\Markdown\LexerRules\PreRule;
use Tempest\Markdown\LexerRules\QuoteRule;
use Tempest\Markdown\LexerRules\ThickRulerRule;
use Tempest\Markdown\LexerRules\ThinRulerRule;
use Tempest\Markdown\Tokens\BoldToken;
use Tempest\Markdown\Tokens\CodeToken;
use Tempest\Markdown\Tokens\ImageToken;
use Tempest\Markdown\Tokens\ItalicToken;
use Tempest\Markdown\Tokens\LinkToken;
use Tempest\Markdown\Tokens\ParagraphToken;
use Tempest\Markdown\Tokens\HeadingToken;
use Tempest\Markdown\Tokens\NewLineToken;
use Tempest\Markdown\Tokens\PreToken;
use Tempest\Markdown\Tokens\QuoteToken;
use Tempest\Markdown\Tokens\RulerToken;
use Tempest\Markdown\Tokens\RulerType;

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

    /** @param null|LexerRule[] $rules */
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

    public function withRules(LexerRule ...$rules): self
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
                if ($rule->shouldLex($lexer)) {
                    $token = $rule->lex($lexer);

                    if ($token) {
                        $tokens[] = $token;
                        $lexer->lastToken = $token;
                    }

                    continue 2;
                }
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

    private function lexParagraph(): Token
    {
        $content = $this->consumeIncluding(self::NEW_LINE);

        return new ParagraphToken($content);
    }

    private function lexHeading(): Token
    {
        $buffer = $this->consumeUntil(self::NEW_LINE);

        $level = strspn($buffer, '#');

        return new HeadingToken(substr($buffer, $level) |> trim(...), $level);
    }

    private function lexNewLine(): Token
    {
        $buffer = $this->consumeWhile(self::NEW_LINE);

        return new NewLineToken($buffer);
    }

    private function lexBold(): Token
    {
        $buffer = $this->consumeWhile('*');
        $buffer .= $this->consumeUntil('*');
        $buffer .= $this->consumeWhile('*');

        return new BoldToken(trim($buffer, '*'));
    }

    private function lexItalic(): Token
    {
        $buffer = $this->consumeWhile('_');
        $buffer .= $this->consumeUntil('_');
        $buffer .= $this->consumeWhile('_');

        return new ItalicToken(trim($buffer, '_'));
    }

    private function lexQuote(): Token
    {
        $buffer = $this->consumeUntil(self::NEW_LINE);

        $level = strspn($buffer, '>');

        return new QuoteToken(substr($buffer, $level) |> trim(...), $level);
    }

    private function lexPre(): Token
    {
        $this->consumeIncluding('```');

        $language = $this->consumeUntil(self::NEW_LINE);

        $this->consumeWhile(self::NEW_LINE);

        $content = $this->consumeUntil('```');

        $this->consumeIncluding('```');
        $this->consumeWhile(self::NEW_LINE);

        return new PreToken(
            language: $language ?: null,
            content: trim($content),
        );
    }

    private function lexCode(): Token
    {
        $this->consumeIncluding('`');

        $content = $this->consumeUntil('`');

        $this->consumeIncluding('`');

        return new CodeToken($content);
    }

    private function lexLink(): Token
    {
        $this->consumeIncluding('[');
        $content = $this->consumeUntil(']');
        $this->consumeIncluding(']');

        $href = null;

        if ($this->comesNext('(')) {
            $this->consumeIncluding('(');
            $href = $this->consumeUntil(')');
            $this->consumeIncluding(')');
        }

        return new LinkToken($content, $href);
    }

    private function lexImage(): Token
    {
        $this->consumeIncluding('![');
        $alt = $this->consumeUntil(']') ?: null;
        $this->consumeIncluding(']');

        if (! $this->comesNext('(')) {
            // TODO: throw error
        }

        $this->consumeIncluding('(');
        $href = $this->consumeUntil(')');
        $this->consumeIncluding(')');

        return new ImageToken($href, $alt);
    }

    private function lexThinRuler(): Token
    {
        $content = $this->consumeWhile('-');

        return new RulerToken(
            $content,
            RulerType::THIN,
        );
    }

    private function lexThickRuler(): Token
    {
        $content = $this->consumeWhile('=');

        return new RulerToken(
            $content,
            RulerType::THICK,
        );
    }
}