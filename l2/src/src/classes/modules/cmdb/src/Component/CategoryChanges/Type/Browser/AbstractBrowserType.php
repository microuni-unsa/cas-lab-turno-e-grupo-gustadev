<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use Exception;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SinglePropertyData;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\AbstractType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use idoit\Module\Cmdb\Component\SyncMerger\Config;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\CollectionDataExtractor;
use idoit\Module\Cmdb\Model\Entry\Entry;
use idoit\Module\Cmdb\Model\Entry\ObjectCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;
use isys_application;
use isys_cmdb_dao;
use isys_cmdb_dao_category;
use isys_tenantsettings;

/**
 * Class AbstractBrowserType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser
 */
abstract class AbstractBrowserType extends AbstractType
{
    const OBJECT_TITLE_WITH_ADDITION    = 'objectTitleWithAddition';
    const OBJECT_TITLE_WITHOUT_ADDITION = 'objectTitleWithoutAddition';

    /**
     * @var int|null
     */
    protected $fromObjectId = null;

    /**
     * @var int|null
     */
    protected $toObjectId = null;

    /**
     * @var string|null
     */
    protected $backwardPropertyTag = null;

    /**
     * @var string|null
     */
    protected $propertyTag = null;

    /**
     * @return int
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
     * @return string|null
     */
    public function getBackwardPropertyTag()
    {
        return $this->backwardPropertyTag;
    }

    /**
     * @param string|null $backwardProperty
     *
     * @return static
     */
    public function setBackwardPropertyTag(?string $backwardProperty = null)
    {
        $this->backwardPropertyTag = $backwardProperty;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPropertyTag()
    {
        return $this->propertyTag;
    }

    /**
     * @param string|null $propertyTag
     *
     * @return static
     */
    public function setPropertyTag(?string $propertyTag = null)
    {
        $this->propertyTag = $propertyTag;
        return $this;
    }

    /**
     * @return string
     */
    protected function getSeparator()
    {
        return isys_tenantsettings::get('gui.separator.connector', ' > ');
    }

    /**
     * @param int           $objectId
     * @param isys_cmdb_dao $dao
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function getObjectTitleAsArray(int $objectId, isys_cmdb_dao $dao)
    {
        $separator = $this->getSeparator();
        $objectData = $dao->get_object($objectId, false, 1)->get_row();
        return [
            self::OBJECT_TITLE_WITH_ADDITION =>
                $this->getLanguage()->get($objectData['isys_obj_type__title']) . $separator . $objectData['isys_obj__title'],
            self::OBJECT_TITLE_WITHOUT_ADDITION =>
                $objectData['isys_obj__title']
        ];
    }

    /**
     * @param string $combinedKey
     *
     * @return SinglePropertyData|null
     * @throws Exception
     */
    public function loadBackwardProperty(string $combinedKey)
    {
        if (strpos($combinedKey, '::') === false) {
            return null;
        }

        [$daoClass, $propertyKey] = explode('::', $combinedKey);

        if (!class_exists($daoClass)) {
            throw new Exception('Category DAO could not be extracted from ' . $combinedKey);
        }
        /**
         * @var isys_cmdb_dao_category $daoObject
         */
        $daoObject = $daoClass::instance(isys_application::instance()->container->get('database'));
        $property = $daoObject->get_property_by_key($propertyKey);

        if ($property && is_array($property)) {
            $property = Property::createInstanceFromArray($property);
        }

        if (!$property instanceof Property) {
            throw new Exception('Property could not be constructed for ' . $combinedKey);
        }
        return new SinglePropertyData($combinedKey, $propertyKey, $daoObject, $property);
    }

    /**
     * Determine the correct category dao class. Because it is possible that there are
     * multiple backwardproperties for one category property
     *
     * @param isys_cmdb_dao_category $dao
     * @param array $list
     * @param int $objectId
     *
     * @return SinglePropertyData|null
     * @throws \isys_exception_database
     */
    public function determineBackwardProperty(isys_cmdb_dao_category $dao, $list, $objectId)
    {
        $newList = array_map(function ($item) use ($dao) {
            return $dao->convert_sql_text(substr($item, 0, strpos($item, '::')));
        }, $list);
        $newList = implode(',', $newList);

        $query = 'SELECT isysgui_catg__class_name as categoryClass FROM isysgui_catg
            INNER JOIN isys_obj_type_2_isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
            INNER JOIN isys_obj_type ON isys_obj_type_2_isysgui_catg__isys_obj_type__id = isys_obj_type__id
            INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_obj__id = ' . $dao->convert_sql_id($objectId) . ' AND isysgui_catg__class_name IN (' . $newList. ')
            UNION
            SELECT isysgui_cats__class_name as categoryClass FROM isysgui_cats
            INNER JOIN isys_obj_type ON isys_obj_type__isysgui_cats__id = isysgui_cats__id
            INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_obj__id = ' . $dao->convert_sql_id($objectId) . ' AND isysgui_cats__class_name IN (' . $newList. ')
            UNION
            SELECT child.isysgui_cats__class_name as categoryClass FROM isysgui_cats child
            INNER JOIN isysgui_cats as parent ON parent.isysgui_cats__id = child.isysgui_cats__parent
            INNER JOIN isys_obj_type ON isys_obj_type__isysgui_cats__id = parent.isysgui_cats__id
            INNER JOIN isys_obj ON isys_obj__isys_obj_type__id = isys_obj_type__id
            WHERE isys_obj__id = ' . $dao->convert_sql_id($objectId) . ' AND child.isysgui_cats__class_name IN (' . $newList. ')
            ';

        $categoryClass = $dao->retrieve($query)->get_row_value('categoryClass');
        $filteredList = array_filter($list, function ($item) use ($categoryClass) {
            return strpos($item, $categoryClass) !== false;
        });

        if (!empty($filteredList)) {
            return $this->loadBackwardProperty((string) current($filteredList));
        }

        return null;
    }

    /**
     * @param string             $currentObjectTitle
     * @param int                $currentObjectId
     * @param SinglePropertyData $backwardProperty
     * @param string             $direction
     * @param int           $objectId
     *
     * @return ChangesData|null
     */
    public function processBackwardsObjectPropertyChange($currentObjectTitle, $currentObjectId, $backwardProperty, $objectId, $direction = TypeInterface::CHANGES_TO)
    {
        $backwardPropertyDao = $backwardProperty->getDao();
        $propertyKey = $backwardProperty->getPropertyKey();

        if ($backwardPropertyDao instanceof ObjectBrowserAssignedEntries) {
            $assignedObjects = $backwardPropertyDao->getAttachedEntries($objectId, $propertyKey);
            $newAssignedObjects = clone $assignedObjects;
            if ($assignedObjects instanceof ObjectCollection) {
                if ($direction === TypeInterface::CHANGES_TO) {
                    $newAssignedObjects->addEntry(ObjectEntry::factory($currentObjectId, $currentObjectTitle, '', '', [], $backwardPropertyDao));
                } else {
                    $newAssignedObjects->unsetEntry($currentObjectId);
                }
            }
            $changesFrom = CollectionDataExtractor::extractPropertyAsArray('title', $assignedObjects);
            $changesTo = CollectionDataExtractor::extractPropertyAsArray('title', $newAssignedObjects);

            if (empty(array_diff($changesTo, $changesFrom)) && empty(array_diff($changesFrom, $changesTo))) {
                return null;
            }

            $changesFrom = implode(', ', $changesFrom);
            $changesTo = implode(', ', $changesTo);
        } else {
            $changesFrom = isys_tenantsettings::get('gui.empty_value', '-');
            $changesTo = $currentObjectTitle;
            if ($direction === TypeInterface::CHANGES_FROM) {
                $changesFrom = $currentObjectTitle;
                $changesTo = $this->getLanguage()->get('LC__UNIVERSAL__CONNECTION_DETACHED');
            }
        }

        $change = ChangesData::factory(
            [
                $backwardProperty->getLogbookChangeKey() => [
                    TypeInterface::CHANGES_FROM => $changesFrom,
                    TypeInterface::CHANGES_TO => $changesTo
                ]
            ],
            $objectId
        );
        $change->setSinglePropertyData($backwardProperty);
        return $change;
    }

    /**
     * @param int $currentObjectId
     * @param string $currentObjectTitle
     * @param Entry $entry
     * @param SinglePropertyData $backwardProperty
     * @param string             $direction
     *
     * @return ChangesData
     */
    public function processBackwardsEntryPropertyChange($currentObjectId, $currentObjectTitle, $entry, $backwardProperty, $direction = TypeInterface::CHANGES_TO)
    {
        $backwardPropertyDao = $backwardProperty->getDao();
        $propertyKey = $backwardProperty->getPropertyKey();
        $changesFrom = isys_tenantsettings::get('gui.empty_value', '-');
        $changesTo = $currentObjectTitle;
        $data = $backwardPropertyDao->getCurrentEntry($entry->getEntryid(), $entry->getObjectid());
        $changes = $this->getChangesAlwaysInLogbook($backwardPropertyDao, $data);

        if ($direction === TypeInterface::CHANGES_TO) {
            if ($data[Config::CONFIG_PROPERTIES][$propertyKey][C__DATA__VALUE]) {
                $objectData = $backwardPropertyDao->get_object($data[Config::CONFIG_PROPERTIES][$propertyKey][C__DATA__VALUE])
                    ->get_row();
                $changesFrom = $objectData['isys_obj__title'];
            }
            $changesTo = $currentObjectTitle;
        }

        if ($direction === TypeInterface::CHANGES_FROM) {
            $changesFrom = $currentObjectTitle;
            $changesTo = $this->getLanguage()->get('LC__UNIVERSAL__CONNECTION_DETACHED');
        }

        $changes[$backwardProperty->getLogbookChangeKey()] = [
            TypeInterface::CHANGES_FROM => $changesFrom,
            TypeInterface::CHANGES_TO => $changesTo
        ];

        $change = ChangesData::factory(
            $changes,
            $entry->getObjectid()
        );
        $change->setSinglePropertyData($backwardProperty);
        return $change;
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param array                  $data
     *
     * @return array
     */
    public function getChangesAlwaysInLogbook(isys_cmdb_dao_category $dao, array $data)
    {
        $changes = [];
        $class = get_class($dao);
        $properties = $dao->get_properties();

        foreach ($properties as $propertyKey => $property) {
            if (!$property instanceof Property) {
                $property = Property::createInstanceFromArray($property);
            }

            if ($property->getInfo()->isAlwaysInLogbook() && isset($data[Config::CONFIG_PROPERTIES][$propertyKey][C__DATA__VALUE])) {
                $changes[$class . '::' . $propertyKey] = [
                    TypeInterface::CHANGES_FROM => $data[Config::CONFIG_PROPERTIES][$propertyKey][C__DATA__VALUE],
                    TypeInterface::CHANGES_TO => $data[Config::CONFIG_PROPERTIES][$propertyKey][C__DATA__VALUE]
                ];
            }
        }
        return $changes;
    }
}
