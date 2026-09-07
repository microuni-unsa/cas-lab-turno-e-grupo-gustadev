<?php

namespace idoit\Module\Cmdb\Model;

use idoit\Component\Provider\Singleton;
use isys_component_database;

/**
 * i-doit
 *
 * Ci Models
 *
 * @package     i-doit
 * @subpackage  Cmdb
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CiTypeCache
{

    /**
     * @var CiType[]
     */
    private $ciTypes = [];

    /**
     * @var Singleton
     */
    private static $instance;

    /**
     * Return instance of current class
     *
     * @param isys_component_database $database
     *
     * @return CiTypeCache
     */
    final public static function instance(isys_component_database $database): CiTypeCache
    {
        if (self::$instance === null) {
            self::$instance = new static($database);
        }

        return self::$instance;
    }

    /**
     * @return CiType[]
     */
    public function getCiTypes()
    {
        return $this->ciTypes;
    }

    /**
     * @param $ciTypeId
     *
     * @return CiType
     */
    public function get($ciTypeId)
    {
        return $this->ciTypes[$ciTypeId] ?: null;
    }

    /**
     * CiTypeCache constructor.
     *
     * @param isys_component_database $database
     */
    public function __construct(isys_component_database $database)
    {
        $result = \isys_cmdb_dao_object_type::instance($database)->get_object_types();

        while ($type = $result->get_row()) {
            $this->ciTypes[$type['isys_obj_type__id']] = CiType::factory($type['isys_obj_type__id'], $type['isys_obj_type__title'], $type['isys_obj_type__const']);

            $this->ciTypes[$type['isys_obj_type__id']]->assignRawData($type);
        }
    }
}
