<?php

namespace idoit\Component\Processor\Dto;

/**
 * Abstract update response.
 */
abstract class AbstractUpdateResponse
{
    public function __construct(public readonly int $id)
    {
    }
}
