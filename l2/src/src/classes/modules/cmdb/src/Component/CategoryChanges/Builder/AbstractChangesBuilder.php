<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Builder;

use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesDataCollection;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DataProvider;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;

/**
 * Class AbstractChangesBuilder
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Builder
 */
abstract class AbstractChangesBuilder
{
    /**
     * @var int|null
     */
    protected $fromObjectId;

    /**
     * @var int|null
     */
    protected $toObjectId;

    /**
     * @var array
     */
    protected $changes = [];

    /**
     * @var ChangesDataCollection
     */
    protected $changesFrom = null;

    /**
     * @var ChangesDataCollection
     */
    protected $changesTo = null;

    /**
     * @var ChangesDataCollection
     */
    protected $changesCurrent = null;

    /**
     * AbstractObjectFormatter constructor.
     */
    public function __construct()
    {
        $this->changesFrom = new ChangesDataCollection();
        $this->changesTo = new ChangesDataCollection();
        $this->changesCurrent = new ChangesDataCollection();
    }

    /**
     * @param DataProvider $dataProvider
     *
     * @return mixed
     */
    abstract public function process(DataProvider $dataProvider);

    /**
     * @return array
     */
    public function getChanges($type = null)
    {
        $changes = [
            TypeInterface::CHANGES_CURRENT => ($this->changesCurrent->hasData() ? $this->changesCurrent->getData() : []),
            TypeInterface::CHANGES_FROM => ($this->changesFrom->hasData() ? $this->changesFrom->getData() : []),
            TypeInterface::CHANGES_TO => ($this->changesTo->hasData() ? $this->changesTo->getData() : [])
        ];

        if ($type === null) {
            return $changes;
        }

        if (!isset($changes[$type])) {
            return [];
        }

        switch ($type) {
            case TypeInterface::CHANGES_TO:
                return $changes[TypeInterface::CHANGES_TO];
            case TypeInterface::CHANGES_FROM:
                return $changes[TypeInterface::CHANGES_FROM];
            case TypeInterface::CHANGES_CURRENT:
                return $changes[TypeInterface::CHANGES_CURRENT];
        }
    }

    /**
     * @param array $changes
     * @return static
     */
    public function setChanges(array $changes)
    {
        $types = [TypeInterface::CHANGES_FROM, TypeInterface::CHANGES_TO, TypeInterface::CHANGES_CURRENT];

        if (empty($changes)) {
            return $this;
        }

        foreach ($types as $type) {
            if (isset($changes[$type])) {
                if (is_array($changes[$type])) {
                    foreach ($changes[$type] as $change) {
                        $this->addChanges($change, $type);
                    }
                    continue;
                }

                $this->addChanges($changes[$type], $type);
            }
        }

        return $this;
    }

    /**
     * @param ChangesData $changes
     * @param string $type
     *
     * @return static
     */
    private function addChanges(ChangesData $changes, string $type)
    {
        switch ($type) {
            case TypeInterface::CHANGES_TO:
                $this->changesTo->addData($changes);
                break;
            case TypeInterface::CHANGES_FROM:
                $this->changesFrom->addData($changes);
                break;
            case TypeInterface::CHANGES_CURRENT:
                $this->changesCurrent->addData($changes);
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFromObjectId()
    {
        return $this->fromObjectId;
    }

    /**
     * @param int|null $fromObjectId
     *
     * @return static
     */
    public function setFromObjectId(?int $fromObjectId = null)
    {
        $this->fromObjectId = $fromObjectId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getToObjectId()
    {
        return $this->toObjectId;
    }

    /**
     * @param int|null $toObjectId
     *
     * @return static
     */
    public function setToObjectId(?int $toObjectId = null)
    {
        $this->toObjectId = $toObjectId;
        return $this;
    }

    /**
     * @return ChangesDataCollection
     */
    public function getChangesFrom(): ?ChangesDataCollection
    {
        return $this->changesFrom;
    }

    /**
     * @param ChangesDataCollection|null $changesFrom
     *
     * @return static
     */
    public function setChangesFrom(?ChangesDataCollection $changesFrom)
    {
        $this->changesFrom = $changesFrom;
        return $this;
    }

    /**
     * @return ChangesDataCollection
     */
    public function getChangesTo()
    {
        return $this->changesTo;
    }

    /**
     * @param ChangesDataCollection|null $changesTo
     *
     * @return static
     */
    public function setChangesTo(?ChangesDataCollection $changesTo)
    {
        $this->changesTo = $changesTo;
        return $this;
    }

    /**
     * @return ChangesDataCollection
     */
    public function getChangesCurrent()
    {
        return $this->changesCurrent;
    }

    /**
     * @param ChangesDataCollection|null $changesCurrent
     *
     * @return static
     */
    public function setChangesCurrent(?ChangesDataCollection $changesCurrent)
    {
        $this->changesCurrent = $changesCurrent;
        return $this;
    }
}
