<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

/**
 * Class ChangesData
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
class ChangesData extends AbstractData
{
    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var SinglePropertyData
     */
    protected $singlePropertyData = null;

    /**
     * @return int
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return ChangesData
     */
    public function setObjectId(int $objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return SinglePropertyData
     */
    public function getSinglePropertyData(): ?SinglePropertyData
    {
        return $this->singlePropertyData;
    }

    /**
     * @param SinglePropertyData $backwardPropertyData
     *
     * @return ChangesData
     */
    public function setSinglePropertyData(SinglePropertyData $backwardPropertyData)
    {
        $this->singlePropertyData = $backwardPropertyData;
        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return static
     */
    public static function factory($data, $objectId)
    {
        $object = new static();
        $object->setData($data);
        $object->setObjectId($objectId);

        return $object;
    }
}
