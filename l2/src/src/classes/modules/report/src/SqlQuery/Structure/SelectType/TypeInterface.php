<?php

namespace idoit\Module\Report\SqlQuery\Structure\SelectType;

use idoit\Component\Property\Property;

interface TypeInterface
{
    public function isApplicable(Property $property): bool;
}
