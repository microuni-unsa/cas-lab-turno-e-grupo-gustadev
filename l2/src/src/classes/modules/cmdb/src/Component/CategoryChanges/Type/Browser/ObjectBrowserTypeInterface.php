<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

/**
 * Interface ObjectBrowserTypeInterface
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser
 */
interface ObjectBrowserTypeInterface
{
    /**
     * @return int|null
     */
    public function getFromObjectId();

    /**
     * @param int|null $fromObjectId
     *
     * @return static
     */
    public function setFromObjectId(?int $fromObjectId = null);

    /**
     * @return int|null
     */
    public function getToObjectId();

    /**
     * @param int|null $toObjectId
     *
     * @return static
     */
    public function setToObjectId(?int $toObjectId = null);

    /**
     * @return string|null
     */
    public function getBackwardPropertyTag();

    /**
     * @param string|null $backwardProperty
     *
     * @return static
     */
    public function setBackwardPropertyTag(?string $backwardProperty = null);

    /**
     * @return string|null
     */
    public function getPropertyTag();

    /**
     * @param string|null $propertyTag
     *
     * @return static
     */
    public function setPropertyTag(?string $propertyTag = null);

    /**
     * @return mixed
     */
    public function handleRanking();
}
