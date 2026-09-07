<?php

namespace idoit\Component\Processor\Dto\Object;

use idoit\Component\Processor\Dto\AbstractReadResponse;

/**
 * Read response for objects.
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
