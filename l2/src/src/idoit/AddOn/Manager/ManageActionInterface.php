<?php

namespace idoit\AddOn\Manager;

interface ManageActionInterface
{
    /**
     * @return bool
     */
    public function process(): bool;
}
