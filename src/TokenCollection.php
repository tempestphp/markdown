<?php

namespace Tempest\Markdown;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<\Tempest\Markdown\Token>
 * @implements ArrayAccess<int, \Tempest\Markdown\Token>
 */
final class TokenCollection implements IteratorAggregate, ArrayAccess
{
    private int $i = 0;

    private array $tokens = [];

    public function __construct(array $tokens = [])
    {
        foreach ($tokens as $token) {
            $this->add($token);
        }
    }

    public function add(Token $token): self
    {
        $this->tokens[$this->i] = $token;

        $this->i++;

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->tokens);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->tokens[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->tokens[$offset] ?? null;
    }

    /** @param int|null $offset */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $offset = $this->i;
        }

        $this->i = $offset + 1;

        $this->tokens[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->tokens[$offset]);
    }
}
