<?php

/**
 * i-doit
 *
 * Licences dao
 *
 * @package     i-doit
 * @subpackage  CMDB_Low-Level_API
 * @author      Dennis Stuecken <dstuecken@synetics.de
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_licences extends isys_component_dao
{
    /**
     * Licence object ID.
     *
     * @var  integer
     */
    private $m_object_id;

    /**
     * DAO constructor.
     *
     * @param   isys_component_database &$p_db
     * @param   integer                 $p_object_id
     *
     * @throws  isys_exception_dao
     */
    public function __construct(isys_component_database &$p_db, $p_object_id)
    {
        if (!is_numeric($p_object_id)) {
            throw new isys_exception_dao("isys_cmbd_dao_licences->__construct() Object-ID is missing!", 0);
        }

        $this->m_object_id = $p_object_id;
        parent::__construct($p_db);
    }

    /**
     * Get the amount of all licences.
     *
     * @param   integer $p_object_id
     *
     * @param null      $p_key_id
     *
     * @return  integer
     */
    public function calculate_sum($p_object_id = null, $p_key_id = null, $type = [])
    {
        $l_row = $this->calculate("amount", $p_object_id, $p_key_id, $type);

        return (empty($l_row["amount"]) ? 0 : $l_row["amount"]);
    }

    /**
     * Get the costs of all licences.
     *
     * @param   integer $p_object_id
     *
     * @return  float
     */
    public function calculate_cost($p_object_id = null)
    {
        $l_row = $this->calculate("cost", $p_object_id);

        return (empty($l_row["cost"]) ? 0 : $l_row["cost"]);
    }

    /**
     * Retrieve the amount of the licences which are currently in use.
     *
     * @param int      $p_cRecStatus
     * @param int|null $l_key_id
     * @param int|null $type
     *
     * @return int
     * @throws isys_exception_database
     */
    public function get_licences_in_use($p_cRecStatus = C__RECORD_STATUS__NORMAL, ?int $l_key_id = null, ?int $type = null)
    {
        $coreBasedLicensesInUse = null;
        if ($type === isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE) {
            return $this->getCpuCoreLicencesInUse($l_key_id, $detailed, $p_cRecStatus);
        }

        $selection = 'count(lic.isys_obj__id) as licences_in_use';
        $condition = [
            'isys_cats_lic_list__type != ' . $this->convert_sql_int(isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE),
            'isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id($this->m_object_id),
            'isys_cats_lic_list__status = ' . $this->convert_sql_int($p_cRecStatus),
            'client.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
            'lic.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
            'isys_catg_application_list__status = ' . $this->convert_sql_int($p_cRecStatus)
        ];

        if ($l_key_id !== null) {
            $condition[] = ' isys_cats_lic_list__id = ' . $this->convert_sql_id($l_key_id);
        }

        $query = 'SELECT
			%s
			FROM isys_catg_application_list
			LEFT JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
			LEFT JOIN isys_obj AS client ON client.isys_obj__id = isys_catg_application_list__isys_obj__id
			LEFT JOIN isys_obj AS lic ON lic.isys_obj__id = isys_cats_lic_list__isys_obj__id
			WHERE %s';

        return $this->retrieve(sprintf($query, $selection, implode(' AND ', $condition)) . ';')
                ->get_row_value('licences_in_use') ?? 0;
    }

    /**
     * @param int      $status
     * @param int|null $id
     * @param int|null $type
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getLicencesInUseDetailed($status = C__RECORD_STATUS__NORMAL, ?int $id = null, ?int $type = null)
    {
        $coreBasedLicensesInUse = null;
        if ($type === isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE) {
            return $this->getCpuCoreLicencesInUseDetailed($id, $status);
        }

        $selection = [
            'client.isys_obj__id                 AS client_obj_id',
            'client.isys_obj__isys_obj_type__id  AS client_obj_type',
            'client.isys_obj__title              AS client_obj_title',
            'lic.isys_obj__id                    AS lic_obj_id',
            'lic.isys_obj__isys_obj_type__id     AS lic_obj_type',
            'lic.isys_obj__title                 AS lic_obj_title'
        ];

        $condition = [
            'isys_cats_lic_list__type != ' . $this->convert_sql_int(isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE),
            'isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id($this->m_object_id),
            'isys_cats_lic_list__status = ' . $this->convert_sql_int($status),
            'client.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
            'lic.isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
            'isys_catg_application_list__status = ' . $this->convert_sql_int($status)
        ];

        if ($id !== null) {
            $condition[] = ' isys_cats_lic_list__id = ' . $this->convert_sql_id($id);
        }

        $query = 'SELECT
			%s
			FROM isys_catg_application_list
			LEFT JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
			LEFT JOIN isys_obj AS client ON client.isys_obj__id = isys_catg_application_list__isys_obj__id
			LEFT JOIN isys_obj AS lic ON lic.isys_obj__id = isys_cats_lic_list__isys_obj__id
			WHERE %s';

        return $this->retrieve(sprintf($query, implode(',', $selection), implode(' AND ', $condition)) . ';');
    }

    /**
     * Get licences from isys_cats_lic_list.
     *
     * @return isys_component_dao_result
     */
    public function get_licences($p_use_obj = false)
    {
        $l_sql = "SELECT * FROM isys_cats_lic_list" . ($p_use_obj ? ' WHERE isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id($this->m_object_id) : '') . ';';

        return $this->retrieve($l_sql);
    }

    /**
     * Do some calculations with sql.
     *
     * @param   string  $p_what
     * @param   integer $p_object_id
     *
     * @param null      $p_key_id
     *
     * @return  array
     * @throws isys_exception_database
     */
    private function calculate($p_what, $p_object_id = null, $p_key_id = null, $types = [])
    {
        switch ($p_what) {
            case "amount":
                $l_select = "SUM(isys_cats_lic_list__amount) AS amount";
                break;
            case "cost":
                $l_select = "SUM(isys_cats_lic_list__amount * isys_cats_lic_list__cost) AS cost";
                break;
            default:
                $l_select = "*";
                break;
        }

        $conditions = [
            'isys_cats_lic_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL),
            'isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id(($p_object_id !== null) ? $p_object_id : $this->m_object_id)
        ];

        if (!empty($types)) {
            $conditions[] = 'isys_cats_lic_list__type ' . $this->prepare_in_condition($types);
        }

        if ($p_key_id !== null) {
            $conditions[] = 'isys_cats_lic_list__id = ' . $this->convert_sql_id($p_key_id);
        }

        $l_sql = 'SELECT ' . $l_select . '
			FROM isys_cats_lic_list
			WHERE ' . implode(' AND ', $conditions);

        return $this->retrieve($l_sql)
            ->get_row();
    }

    /**
     * @param int|null $id
     * @param false    $groupBy
     * @param int      $status
     *
     * @return int
     * @throws isys_exception_database
     */
    public function getCpuCoreLicencesInUse(?int $id = null, $groupBy = false, $status = C__RECORD_STATUS__NORMAL)
    {
        $selection = 'SUM(isys_catg_cpu_list__cores) AS licenced_cores';
        $conditions = [
            'isys_cats_lic_list__type = ' . $this->convert_sql_int(isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE),
            'isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id($this->m_object_id),
            'isys_cats_lic_list__status = ' . $this->convert_sql_int($status),
            'isys_catg_cpu_list__status = ' . $this->convert_sql_int($status),
            'isys_obj__status = ' . $this->convert_sql_int($status)
        ];

        if ($id !== null) {
            $conditions[] = ' isys_cats_lic_list__id = ' . $this->convert_sql_id($id);
        }

        $query = 'SELECT 
            %s
            FROM isys_catg_cpu_list
            INNER JOIN isys_obj ON isys_catg_cpu_list__isys_obj__id = isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            INNER JOIN isys_catg_application_list ON isys_catg_application_list__isys_obj__id = isys_obj__id
            INNER JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
            WHERE %s;';

        $query = sprintf(
            $query,
            $selection,
            implode(' AND ', $conditions)
        );

        return $this->retrieve($query)->get_row_value('licenced_cores') ?? 0;
    }

    /**
     * @param int      $status
     * @param int|null $id
     *
     * @return isys_component_dao_result
     * @throws isys_exception_database
     */
    public function getCpuCoreLicencesInUseDetailed(?int $id = null, $status = C__RECORD_STATUS__NORMAL)
    {
        $selection = [
            'SUM(isys_catg_cpu_list__cores) AS licenced_cores',
            'client.isys_obj__id as client_obj_id',
            'client.isys_obj__isys_obj_type__id as client_obj_type',
            'client.isys_obj__title as client_obj_title',
            'lic.isys_obj__id                    AS lic_obj_id',
            'lic.isys_obj__isys_obj_type__id     AS lic_obj_type',
            'lic.isys_obj__title                 AS lic_obj_title'
        ];
        $conditions = [
            'isys_cats_lic_list__type = ' . $this->convert_sql_int(isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE),
            'isys_cats_lic_list__isys_obj__id = ' . $this->convert_sql_id($this->m_object_id),
            'isys_cats_lic_list__status = ' . $this->convert_sql_int($status),
            'isys_catg_cpu_list__status = ' . $this->convert_sql_int($status),
            'client.isys_obj__status = ' . $this->convert_sql_int($status)
        ];
        $groupSelection = ' GROUP BY client.isys_obj__id';

        if ($id !== null) {
            $conditions[] = ' isys_cats_lic_list__id = ' . $this->convert_sql_id($id);
        }

        $query = 'SELECT 
            %s
            FROM isys_catg_cpu_list
            INNER JOIN isys_obj as client ON isys_catg_cpu_list__isys_obj__id = client.isys_obj__id
            INNER JOIN isys_obj_type ON isys_obj_type__id = client.isys_obj__isys_obj_type__id
            INNER JOIN isys_catg_application_list ON isys_catg_application_list__isys_obj__id = client.isys_obj__id
            INNER JOIN isys_cats_lic_list ON isys_cats_lic_list__id = isys_catg_application_list__isys_cats_lic_list__id
			LEFT JOIN isys_obj AS lic ON lic.isys_obj__id = isys_cats_lic_list__isys_obj__id
            WHERE %s %s';

        $query = sprintf(
            $query,
            implode(',', $selection),
            implode(' AND ', $conditions),
            $groupSelection
        ) . ';';

        return $this->retrieve($query);
    }
}
