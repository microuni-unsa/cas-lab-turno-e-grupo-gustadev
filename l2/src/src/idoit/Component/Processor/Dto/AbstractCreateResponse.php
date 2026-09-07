<?php

namespace idoit\Component\Processor\Dto;

/**
 * Abstract create response.
 */
abstract class AbstractCreateResponse
{
    public function __construct(public readonly int $id)
    {
    }
}
