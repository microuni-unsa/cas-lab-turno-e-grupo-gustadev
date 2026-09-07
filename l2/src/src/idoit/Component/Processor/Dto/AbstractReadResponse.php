<?php

namespace idoit\Component\Processor\Dto;

/**
 * Abstract read response.
 */
abstract class AbstractReadResponse
{
    public readonly array $entries;

    public function __construct(array $entries)
    {
        $this->entries = array_values($entries);
    }

    public function first(): ?object
    {
        return $this->entries[0] ?? null;
    }

    public function last(): ?object
    {
        return $this->entries[$this->total() - 1] ?? null;
    }

    public function total(): int
    {
        return count($this->entries);
    }
}
