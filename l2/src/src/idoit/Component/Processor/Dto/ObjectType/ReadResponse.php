<?php

namespace idoit\Component\Processor\Dto\ObjectType;

use idoit\Component\Processor\Dto\AbstractReadResponse;

/**
 * Read response for object types.
 */
class ReadResponse extends AbstractReadResponse
{
    public function first(): ?Dto
    {
        return parent::first();
    }

    public function last(): ?Dto
    {
        return parent::last();
    }
}
