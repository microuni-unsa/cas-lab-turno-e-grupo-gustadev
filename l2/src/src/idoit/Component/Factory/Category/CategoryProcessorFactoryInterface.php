<?php

namespace idoit\Component\Factory\Category;

use idoit\Component\Processor\Category\CategoryProcessorInterface;

/**
 * Category processor factory interface.
 */
interface CategoryProcessorFactoryInterface
{
    /**
     * @param string $constant
     *
     * @return bool
     */
    public function supports(string $constant): bool;

    /**
     * @param string $categoryConstant
     *
     * @return CategoryProcessorInterface
     */
    public function create(string $categoryConstant): CategoryProcessorInterface;
}
