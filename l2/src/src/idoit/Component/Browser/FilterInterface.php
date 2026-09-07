<?php

namespace idoit\Component\Browser;

interface FilterInterface
{
    /**
     * Parameter setter.
     *
     * @param mixed $parameter
     *
     * @return $this
     */
    public function setParameter($parameter): static;

    /**
     * Parameter getter.
     *
     * @return mixed
     */
    public function getParameter(): mixed;

    /**
     * Method for retrieving a query condition by a provided parameter.
     *
     * @return string
     */
    public function getQueryCondition(): string;

    /**
     * This method will decide if the filterVisitor should be called.
     *
     * @return boolean
     */
    public function hasVisitor(): bool;

    /**
     * Method for filtering the result.
     *
     * @param array $objects
     *
     * @return array
     */
    public function visit(array $objects): array;
}
