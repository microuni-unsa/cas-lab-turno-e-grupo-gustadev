<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\ChangesData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\DefaultData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\RequestData;
use idoit\Module\Cmdb\Component\CategoryChanges\Data\SmartyData;
use isys_cmdb_dao_category;

/**
 * Interface TypeInterface
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
interface TypeInterface
{
    const CHANGES_FROM = 'from';
    const CHANGES_TO = 'to';
    const CHANGES_CURRENT = 'current';
    const CHANGES_BOTH = 'both';

    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class);

    /**
     * @param string $tag
     * @param isys_cmdb_dao_category $dao
     * @param RequestData $requestDataProvider
     * @param SmartyData $smartyDataProvider
     * @param array $currentData
     * @param array $propertiesAlwaysInLogbook
     *
     * @return array
     */
    public function handlePostData(
        string $tag,
        isys_cmdb_dao_category $dao,
        RequestData $requestDataProvider,
        SmartyData $smartyDataProvider,
        array $currentData = [],
        array $propertiesAlwaysInLogbook = []
    );

    /**
     * @param DefaultData $currentDataProvider
     * @param DefaultData $changedDataProvider
     *
     * @return array
     */
    public function handleData(
        string $tag,
        isys_cmdb_dao_category $dao,
        DefaultData $currentDataProvider,
        DefaultData $changedDataProvider,
        array $propertiesAlwaysInLogbook = []
    );

    /**
     * @param string                 $tag
     * @param isys_cmdb_dao_category $dao
     *
     * @return ChangesData|null
     */
    public function getChangesWithDefaults(string $tag, isys_cmdb_dao_category $dao);

    /**
     * @return Property
     */
    public function getProperty();

    /**
     * @param Property $property
     *
     * @return mixed
     */
    public function setProperty(Property $property);
}
