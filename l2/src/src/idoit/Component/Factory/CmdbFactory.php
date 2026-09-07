<?php

namespace idoit\Component\Factory;

use idoit\Component\Factory\Category\CollectionFactory;
use idoit\Component\Factory\Category\TaggedFactory;
use idoit\Component\Processor\Category\CategoryProcessorInterface;
use idoit\Component\Processor\Object\ObjectProcessor;
use idoit\Component\Processor\ObjectType\ObjectTypeProcessor;
use idoit\Component\Processor\ObjectTypeGroup\ObjectTypeGroupProcessor;
use idoit\Exception\Exception;
use isys_cmdb_dao;
use isys_component_template_language_manager;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Category factory component to establish a unified internal API to apply CRUD operations on categories.
 */
final class CmdbFactory
{
    /**
     * @param isys_cmdb_dao                            $dao
     * @param isys_component_template_language_manager $language
     * @param UrlGenerator                             $routeGenerator
     * @param TaggedFactory                            $taggedFactory
     * @param CollectionFactory                        $collectionFactory
     */
    public function __construct(
        private isys_cmdb_dao $dao,
        private isys_component_template_language_manager $language,
        private UrlGenerator $routeGenerator,
        private TaggedFactory $taggedFactory,
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @param CategoryProcessorInterface $processor
     *
     * @return $this
     */
    public function registerCategoryProcessor(CategoryProcessorInterface $processor): self
    {
        $this->taggedFactory->registerCategoryProcessor($processor);

        return $this;
    }

    /**
     * @param string $categoryConstant
     *
     * @return CategoryProcessorInterface
     * @throws Exception
     */
    public function getCategoryProcessor(string $categoryConstant): CategoryProcessorInterface
    {
        // @todo Remove this once everything is ready :)
        throw new Exception('Category processors are not implemented yet.');

        if ($this->taggedFactory->supports($categoryConstant)) {
            return $this->taggedFactory->create($categoryConstant);
        }

        return $this->collectionFactory->create($categoryConstant);
    }

    /**
     * Create a object processor for any object related action.
     *
     * @return ObjectProcessor
     */
    public function getObjectProcessor(): ObjectProcessor
    {
        return ObjectProcessor::instance($this->dao, $this->language, $this->routeGenerator);
    }

    /**
     * Create a object type processor for any object related action.
     *
     * @return ObjectTypeProcessor
     */
    public function getObjectTypeProcessor(): ObjectTypeProcessor
    {
        return ObjectTypeProcessor::instance($this->dao, $this->language, $this->routeGenerator);
    }

    /**
     * Create a object type processor for any object related action.
     *
     * @return ObjectTypeGroupProcessor
     */
    public function getObjectTypeGroupProcessor(): ObjectTypeGroupProcessor
    {
        return ObjectTypeGroupProcessor::instance($this->dao, $this->language, $this->routeGenerator);
    }
}
