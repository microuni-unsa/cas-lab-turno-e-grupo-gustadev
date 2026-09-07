<?php

namespace idoit\Component\Processor\Dto;

/**
 * Abstract rank response.
 */
abstract class AbstractRankResponse
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}
