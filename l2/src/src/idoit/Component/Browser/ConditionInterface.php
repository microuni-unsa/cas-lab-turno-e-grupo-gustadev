<?php

namespace idoit\Component\Browser;

interface ConditionInterface
{
    /**
     * @param mixed $parameter
     *
     * @return $this
     */
    public function setParameter($parameter): static;

    /**
     * The "context object ID" can be used by some specific conditions.
     *
     * @param integer $contextObjectId
     *
     * @return $this
     */
    public function setContextObjectId($contextObjectId): static;

    /**
     * @param bool $displayCount
     *
     * @return $this
     */
    public function enableObjectCount($displayCount): static;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * Method for retrieving the object overview (available parameters).
     *
     * @return array
     */
    public function retrieveOverview(): array;

    /**
     * Method for retrieving the objects.
     *
     * @return array
     */
    public function retrieveObjects(): array;

    /**
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function registerFilter(FilterInterface $filter): static;

    /**
     * Convenience method for registering multiple filters at once.
     *
     * @param array $filterData
     *
     * @return $this
     */
    public function registerFilterByArray(array $filterData): static;

    /**
     * Method to iterate over all filter visitors, passing the objects from one visitor to the next and thereby reducing the amount of resulting objects.
     *
     * @param array $objects
     *
     * @return array
     */
    public function processVisitors(array $objects): array;

    /**
     * Method that defines if the object order should be retained (necessary for "date" condition).
     *
     * @return boolean
     */
    public function retainObjectOrder(): bool;
}
