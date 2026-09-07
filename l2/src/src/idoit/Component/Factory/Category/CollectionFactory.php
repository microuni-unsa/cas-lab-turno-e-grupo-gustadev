<?php

namespace idoit\Component\Factory\Category;

use idoit\Component\Processor\Category\CategoryProcessorInterface;
use idoit\Exception\Exception;

/**
 * Collection factory to iterate over multiple passed factories in order to create a category processor.
 */
class CollectionFactory implements CategoryProcessorFactoryInterface
{
    private array $factories;

    /**
     * @param array $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * @param string $categoryConstant
     *
     * @return bool
     */
    public function supports(string $categoryConstant): bool
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($categoryConstant)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $categoryConstant
     *
     * @return CategoryProcessorInterface
     * @throws Exception
     */
    public function create(string $categoryConstant): CategoryProcessorInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($categoryConstant)) {
                return $factory->create($categoryConstant);
            }
        }

        throw new Exception("The given category ({$categoryConstant}) could not be created by CollectionFactory.");
    }
}
