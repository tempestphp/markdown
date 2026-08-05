<?php

namespace Tempest\Markdown;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, \Tempest\Markdown\ParsedMarkdown>
 * @implements ArrayAccess<int|string, \Tempest\Markdown\ParsedMarkdown>
 */
final class ParsedMarkdownCollection implements IteratorAggregate, ArrayAccess, Countable
{
    /** @var list<ParsedMarkdown> */
    private array $chunks = [];

    /** @var array<string, int> */
    private array $byName = [];

    public function __construct(array $chunks = [])
    {
        foreach ($chunks as $chunk) {
            $this->add($chunk);
        }
    }

    public function add(ParsedMarkdown $chunk): self
    {
        $index = count($this->chunks);
        $this->chunks[$index] = $chunk;

        if ($chunk->name !== null) {
            $this->byName[$chunk->name] = $index;
        }

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->chunks);
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) ? isset($this->byName[$offset]) : isset($this->chunks[$offset]);
    }

    public function offsetGet(mixed $offset): ?ParsedMarkdown
    {
        if (is_string($offset)) {
            return isset($this->byName[$offset]) ? $this->chunks[$this->byName[$offset]] : null;
        }

        return $this->chunks[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset !== null) {
            throw new InvalidArgumentException(
                'ParsedMarkdownCollection does not support setting an explicit offset. '
                . 'Use $collection[] = $chunk or add($chunk) instead — a chunk\'s name '
                . '(if any) is what makes it reachable by name, not the assignment key.',
            );
        }

        $this->add($value);
    }

    public function offsetUnset(mixed $offset): void
    {
        // unsupported
    }

    public function count(): int
    {
        return count($this->chunks);
    }
}
