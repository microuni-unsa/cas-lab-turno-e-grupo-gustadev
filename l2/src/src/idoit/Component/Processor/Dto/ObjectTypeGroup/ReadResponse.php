<?php

namespace idoit\Component\Processor\Dto\ObjectTypeGroup;

use idoit\Component\Processor\Dto\AbstractReadResponse;

/**
 * Read response for object type groups.
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
