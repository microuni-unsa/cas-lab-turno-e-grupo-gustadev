<?php

namespace idoit\Module\Cmdb\Interfaces;

use ArrayObject;

interface EntryInterface
{
    /**
     * @return ArrayObject|null
     */
    public function getData(): ?ArrayObject;
}
