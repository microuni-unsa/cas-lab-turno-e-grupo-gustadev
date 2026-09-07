<?php

/**
 * i-doit
 *
 * List DAO: Relations.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Leonard Fischer <lfischer@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_cmdb_dao_list_objects_relation extends isys_cmdb_dao_list_objects
{

    /**
     * Array with isys_weighting content
     *
     * id => title
     *
     * @var array
     */
    private $m_weightings = [];

    /**
     * Cached record status numbers.
     * @var array|null
     */
    private null|array $recordStatusCount = null;


    /**
     * Method for retrieving additional conditions to a object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_conditions()
    {
        $l_return = '';

        if (isset($_GET["type"])) {
            $l_return = " AND (rel_type.isys_catg_relation_list__isys_relation_type__id = " . $this->convert_sql_id($_GET["type"]) . ") ";
        }

        return $l_return . parent::get_additional_conditions();
    }

    /**
     * Method for retrieving additional joins to an object type.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_additional_joins()
    {
        $joins = parent::get_additional_joins();
        if (isset($_GET["type"])) {
            return $joins . ' INNER JOIN isys_catg_relation_list rel_type ON rel_type.isys_catg_relation_list__isys_obj__id = obj_main.isys_obj__id';
        }
        return $joins;
    }

    /**
     * Method for retrieving the default JSON encoded array of the property-selector.
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function get_default_list_config()
    {
        return '[[' . C__PROPERTY_TYPE__STATIC . ',"title","isys_obj__title","LC__UNIVERSAL__RELATION","isys_cmdb_dao_category_g_relation::get_properties_ng",["isys_cmdb_dao_category_g_relation",false]],' .
            '[' . C__PROPERTY_TYPE__STATIC . ',"relation_type","isys_relation_type__title","LC__CATG__RELATION__RELATION_TYPE","isys_cmdb_dao_category_g_relation::get_properties_ng",false],' .
            '[' . C__PROPERTY_TYPE__STATIC . ',"weighting","isys_weighting__title","LC__CATG__RELATION__WEIGHTING","isys_cmdb_dao_category_g_relation::get_properties_ng",false],' .
            '[' . C__PROPERTY_TYPE__DYNAMIC . ',"_itservice",false,"LC__CMDB__CATG__IT_SERVICE","isys_cmdb_dao_category_g_relation::get_dynamic_properties",["isys_cmdb_dao_category_g_relation","dynamic_property_callback_itservice"]]]';
    }

    /**
     * Implement list specific logic.
     *
     * @return array
     * @see ID-11533 Implement class specific logic.
     */
    public function get_rec_counts()
    {
        // @see ID-11230 Work with cached values, if this data was already fetched.
        if (isset($this->recordStatusCount)) {
            return $this->recordStatusCount;
        }

        // Build SQL-Statement
        $query = 'SELECT
            SUM(isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . ') AS COUNT_NORMAL,
            SUM(isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__ARCHIVED) . ') AS COUNT_ARCHIVED,
            SUM(isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__DELETED) . ') AS COUNT_DELETED ';

        // Add status C__TEMPLATE__STATUS if defined
        if (defined("C__TEMPLATE__STATUS") && C__TEMPLATE__STATUS == 1) {
            $query .= ', SUM(isys_obj__status = ' . $this->convert_sql_int(C__RECORD_STATUS__TEMPLATE) . ') AS COUNT_TEMPLATE ';
        }

        $query .= 'FROM isys_obj AS obj_main
            ' . $this->get_additional_joins() . '
            WHERE isys_obj__isys_obj_type__id = ' . $this->convert_sql_id($this->m_object_type['isys_obj_type__id']) . '
            ' . $this->get_additional_conditions() . '
            ' . $this->prepare_status_filter() . '
            ' . isys_auth_cmdb_objects::instance()->get_allowed_objects_condition(isys_auth::VIEW) . ';';

        // Retrieve results
        $row = $this->retrieve($query)->get_row();

        // Build array
        $this->recordStatusCount = [
            C__RECORD_STATUS__NORMAL   => (int)$row['COUNT_NORMAL'],
            C__RECORD_STATUS__ARCHIVED => (int)$row['COUNT_ARCHIVED'],
            C__RECORD_STATUS__DELETED  => (int)$row['COUNT_DELETED'],
        ];

        // Add C__STATUS__TEMPLATE
        if (isset($row['COUNT_TEMPLATE'])) {
            $this->recordStatusCount[C__RECORD_STATUS__TEMPLATE] = (int)$row['COUNT_TEMPLATE'];
        }

        return $this->recordStatusCount;
    }

    /**
     * Relation list constructor
     */
    public function __construct($p_db)
    {
        parent::__construct($p_db);

        /* Set memory limit */
        if (($l_memlimit = isys_tenantsettings::get('system.memory-limit.relation-object-list', '1024M'))) {
            ini_set('memory_limit', $l_memlimit);
        }
        $this->deactivate_group_by();
        $this->deactivate_order_by();
        // Cache weighting for a better query performance
        $l_q = $this->m_cat_dao->retrieve('SELECT isys_weighting__id AS id, isys_weighting__title AS title FROM isys_weighting');
        while ($l_r = $l_q->get_row()) {
            $this->m_weightings[$l_r['id']] = $l_r['title'];
        }
    }
}
