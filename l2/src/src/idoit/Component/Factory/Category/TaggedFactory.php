<?php

namespace idoit\Component\Factory\Category;

use idoit\Component\Processor\Category\CategoryProcessorInterface;
use idoit\Exception\Exception;

/**
 * Tagged factory to create specifically created and registered category processors.
 */
class TaggedFactory implements CategoryProcessorFactoryInterface
{
    /** @var array<CategoryProcessorInterface>  */
    private array $processors = [];

    /**
     * @param iterable $processors
     */
    public function __construct(iterable $processors)
    {
        foreach ($processors as $processor) {
            $this->registerCategoryProcessor($processor);
        }
    }

    /**
     * @param CategoryProcessorInterface $processor
     *
     * @return $this
     */
    public function registerCategoryProcessor(CategoryProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @param string $categoryConstant
     *
     * @return bool
     */
    public function supports(string $categoryConstant): bool
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports($categoryConstant)) {
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
        foreach ($this->processors as $processor) {
            if ($processor->supports($categoryConstant)) {
                return $processor;
            }
        }

        throw new Exception("The given category ({$categoryConstant}) could not be created by TaggedFactory.");
    }
}
