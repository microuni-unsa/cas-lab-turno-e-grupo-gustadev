<?php

use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: specific category for operating systems
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_operating_system extends isys_cmdb_dao_category_s_application
{
    /**
     * @var string
     */
    protected $m_tpl = 'cats__operating_system.tpl';

    /**
     * @var string
     */
    protected $m_table = 'isys_cats_application_list';

    /** @var string */
    protected $categoryTitle = 'LC__CMDB__CATS__OPERATING_SYSTEM';

    /**
     * Get category constant
     *
     * @return string
     */
    public function get_category_const()
    {
        /**
         * We overwrite this to force handling as independent category
         * which uses all procedures of application category
         */
        return 'C__CATS__OPERATING_SYSTEM';
    }

    /**
     * Get category id
     *
     * @return int
     */
    public function get_category_id()
    {
        if (defined('C__CATS__OPERATING_SYSTEM')) {
            return constant('C__CATS__OPERATING_SYSTEM');
        }
    }

    /**
     * @return array
     */
    protected function properties()
    {
        $properties = parent::properties();

        $properties['osFamily'] = new TextProperty(
            'C__CATS__APPLICATION_OS_FAMILY',
            'LC__CMDB__CATS__OPERATION_SYSTEM__FAMILY',
            'isys_cats_application_list__os_family',
            'isys_cats_application_list'
        );

        $properties['systemType'] = new TextProperty(
            'C__CATS__APPLICATION_SYSTEM_TYPE',
            'LC__CMDB__CATS__OPERATION_SYSTEM__TYPE',
            'isys_cats_application_list__system_type',
            'isys_cats_application_list'
        );

        return $properties;
    }

    /**
     * @param int $p_cat_level
     * @param int $p_intOldRecStatus
     *
     * @return int|mixed
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $id = parent::save_element($p_cat_level, $p_intOldRecStatus);

        if ($id > 0) {
            $this->updateEntry($id, $_POST['C__CATS__APPLICATION_OS_FAMILY'], $_POST['C__CATS__APPLICATION_SYSTEM_TYPE']);
        }

        return $id;
    }

    /**
     * @param int $id
     * @param string|null $osFamily
     * @param string|null $systemType
     *
     * @return bool
     * @throws isys_exception_dao
     */
    private function updateEntry($id, $osFamily = null, $systemType = null)
    {
        $update = 'UPDATE isys_cats_application_list SET
                isys_cats_application_list__os_family = ' . $this->convert_sql_text($osFamily) . ',
                isys_cats_application_list__system_type = ' . $this->convert_sql_text($systemType) . '
                WHERE isys_cats_application_list__id = ' . $this->convert_sql_id($id);

        return $this->update($update) && $this->apply_update();
    }

    /**
     * @param int    $p_cat_level
     * @param int    $p_newRecStatus
     * @param string $p_specification
     * @param int    $p_manufacturerID
     * @param string $p_release
     * @param string $p_description
     * @param null   $p_installation_type
     * @param null   $p_registration_key
     * @param null   $p_install_path
     * @param null   $osFamily
     * @param null   $systemType
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function save(
        $p_cat_level,
        $p_newRecStatus,
        $p_specification,
        $p_manufacturerID,
        $p_release,
        $p_description,
        $p_installation_type = null,
        $p_registration_key = null,
        $p_install_path = null,
        $osFamily = null,
        $systemType = null
    ) {
        if (!parent::save(
            $p_cat_level,
            $p_newRecStatus,
            $p_specification,
            $p_manufacturerID,
            $p_release,
            $p_description,
            $p_installation_type,
            $p_registration_key,
            $p_install_path
        )
        ) {
            return false;
        }

        return $this->updateEntry($p_cat_level, $osFamily, $systemType);
    }

    /**
     * @param      $objID
     * @param      $newRecStatus
     * @param      $specification
     * @param      $manufacturerID
     * @param      $release
     * @param      $description
     * @param null $installation_type
     * @param null $registration_key
     * @param null $install_path
     * @param null $osFamily
     * @param null $systemType
     *
     * @return false|mixed
     * @throws isys_exception_dao
     */
    public function create(
        $objID,
        $newRecStatus,
        $specification,
        $manufacturerID,
        $release,
        $description = '',
        $installation_type = null,
        $registration_key = null,
        $install_path = null,
        $osFamily = null,
        $systemType = null
    ) {
        $id = parent::create($objID, $newRecStatus, $specification, $manufacturerID, $release, $description, $installation_type, $registration_key, $install_path);

        if (!$id) {
            return false;
        }

        $this->updateEntry($id, $osFamily, $systemType);

        return $id;
    }

    /**
     * @param array $p_category_data
     * @param int   $p_object_id
     * @param int   $p_status
     *
     * @return false|mixed
     * @throws isys_exception_dao
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1)
    {
        $id = parent::sync($p_category_data, $p_object_id, $p_status);

        if ($id > 0) {
            $this->updateEntry(
                $id,
                $p_category_data['properties']['osFamily'][C__DATA__VALUE] ?? null,
                $p_category_data['properties']['systemType'][C__DATA__VALUE] ?? null
            );
        }
        return $id;
    }
}
