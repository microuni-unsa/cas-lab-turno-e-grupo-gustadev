<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Property;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DateProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\DialogProperty;
use idoit\Component\Property\Type\DialogYesNoProperty;
use idoit\Component\Property\Type\ObjectBrowserProperty;
use idoit\Component\Property\Type\TextAreaProperty;
use idoit\Component\Property\Type\TextProperty;
use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\ObjectCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * i-doit
 *
 * DAO: global category for SIM cards
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_sim_card extends isys_cmdb_dao_category_global implements ObjectBrowserAssignedEntries
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'sim_card';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATS__SIM_CARD';
        $this->m_is_purgable = true;
    }

    /**
     * Dynamic property handling for retrieving the object ID.
     *
     * @param array $row
     * @return string
     * @throws isys_exception_database
     */
    public function dynamic_property_callback_assigned_mobile(array $row)
    {
        $return = '';

        $dao = self::instance(isys_application::instance()->container->get('database'));

        $entryRow = $dao->get_data(null, $row['isys_obj__id'])->get_row();

        if ($entryRow !== false && $entryRow['isys_catg_assigned_cards_list__isys_obj__id'] > 0) {
            $cellphoneData = $dao->get_object_by_id($entryRow['isys_catg_assigned_cards_list__isys_obj__id'])->get_row();

            $return = (new isys_ajax_handler_quick_info())->get_quick_info(
                $cellphoneData['isys_obj__id'],
                isys_application::instance()->container->get('language')->get($cellphoneData['isys_obj_type__title']) . ' &raquo; ' . $cellphoneData['isys_obj__title'],
                C__LINK__OBJECT
            );
        }

        return $return;
    }

    /**
     * @param array $categoryData
     * @return mixed
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function create_data($categoryData)
    {
        // Remember the assigned mobile and unset it, since the generic logic will produce an SQL error.
        $assignedMobile = $categoryData['assigned_mobile'];

        unset($categoryData['assigned_mobile']);

        $returnValue = parent::create_data($categoryData);

        if (is_numeric($returnValue)) {
            // After saving the data, proceed with the assigned mobile.
            $assignedCardsDao = isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component());

            $objectId = $this->get_data($returnValue)->get_row_value('isys_catg_sim_card_list__isys_obj__id');

            if ($objectId > 0) {
                $assignedCardsDao->remove_component(null, $objectId);

                if ($assignedMobile > 0) {
                    $assignedCardsDao->add_component($assignedMobile, $objectId);
                }
            }
        }

        return $returnValue;
    }

    /**
     * @param int   $categoryEntryId
     * @param array $categoryData
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     */
    public function save_data($categoryEntryId, $categoryData)
    {
        // Remember the assigned mobile and unset it, since the generic logic will produce an SQL error.
        $assignedMobile = $categoryData['assigned_mobile'];

        unset($categoryData['assigned_mobile']);

        $returnValue = parent::save_data($categoryEntryId, $categoryData);

        // After saving the data, proceed with the assigned mobile.
        $assignedCardsDao = isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component());

        $objectId = $this->get_data($categoryEntryId)->get_row_value('isys_catg_sim_card_list__isys_obj__id');

        if ($objectId > 0) {
            $assignedCardsDao->remove_component(null, $objectId);

            if ($assignedMobile > 0) {
                $assignedCardsDao->add_component($assignedMobile, $objectId);
            }
        }

        return $returnValue;
    }

    /**
     * @inheritDoc
     */
    public function get_data($entryId = null, $objectId = null, $condition = '', $filter = null, $status = null)
    {
        $sql = 'SELECT * FROM isys_catg_sim_card_list
            LEFT OUTER JOIN isys_cp_contract_type ON isys_catg_sim_card_list__isys_cp_contract_type__id = isys_cp_contract_type__id
            LEFT OUTER JOIN isys_network_provider ON isys_catg_sim_card_list__isys_network_provider__id = isys_network_provider__id
            LEFT OUTER JOIN isys_telephone_rate ON isys_catg_sim_card_list__isys_telephone_rate__id = isys_telephone_rate__id
            LEFT OUTER JOIN isys_catg_assigned_cards_list ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_catg_sim_card_list__isys_obj__id
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_sim_card_list__isys_obj__id
            WHERE TRUE ' . $condition . ' ' . $this->prepare_filter($filter);

        if ($objectId !== null) {
            $sql .= $this->get_object_condition($objectId);
        }

        if ($entryId !== null) {
            $sql .= ' AND isys_catg_sim_card_list__id = ' . $this->convert_sql_id($entryId);
        }

        if ($status !== null) {
            $sql .= ' AND isys_catg_sim_card_list__status = ' . $this->convert_sql_int($status);
        }

        return $this->retrieve($sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        /** @var isys_component_template_language_manager $language */
        $language = isys_application::instance()->container->get('language');

        return [
            'type' => new DialogProperty(
                'C__CATS__CP_CONTRACT__TYPE',
                'LC__CMDB__CATG__TYPE',
                'isys_catg_sim_card_list__isys_cp_contract_type__id',
                'isys_catg_sim_card_list',
                'isys_cp_contract_type'
            ),
            'assigned_mobile'  => (new ObjectBrowserProperty(
                'C__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE',
                'LC__CMDB__CATS__SIM_CARD__ASSIGNED_MOBILE_PHONE',
                'isys_catg_assigned_cards_list__isys_obj__id',
                'isys_catg_assigned_cards_list',
                [],
                'C__CATG__ASSIGNED_CARDS',
            ))->mergePropertyData([
                Property::C__PROPERTY__DATA__SELECT => SelectSubSelect::factory(
                    'SELECT CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                        FROM isys_catg_assigned_cards_list
                        INNER JOIN isys_obj ON isys_obj__id = isys_catg_assigned_cards_list__isys_obj__id',
                    'isys_catg_assigned_cards_list',
                    'isys_catg_assigned_cards_list__id',
                    'isys_catg_assigned_cards_list__isys_obj__id__card',
                    '',
                    '',
                    null,
                    null,
                    '',
                    1
                )
            ])->mergePropertyProvides([
                Property::C__PROPERTY__PROVIDES__REPORT => true,
                Property::C__PROPERTY__PROVIDES__LIST => true,
                Property::C__PROPERTY__PROVIDES__MULTIEDIT => false,
            ]),
            'network_provider' => new DialogPlusProperty(
                'C__CATS__CP_CONTRACT__NETWORK_PROVIDER',
                'LC__CMDB__CATS_CP_CONTRACT__NETWORK_PROVIDER',
                'isys_catg_sim_card_list__isys_network_provider__id',
                'isys_catg_sim_card_list',
                'isys_network_provider'
            ),
            'telephone_rate' => new DialogPlusProperty(
                'C__CATS__CP_CONTRACT__TELEPHONE_RATE',
                'LC__CMDB__CATS_CP_CONTRACT__TELEPHONE_RATE',
                'isys_catg_sim_card_list__isys_telephone_rate__id',
                'isys_catg_sim_card_list',
                'isys_telephone_rate'
            ),
            'start'            => new DateProperty(
                'C__CATS__CP_CONTRACT__START_DATE',
                'LC__CMDB__CATS_CP_CONTRACT__START_DATE',
                'isys_catg_sim_card_list__start_date',
                'isys_catg_sim_card_list',
            ),
            'end'              => new DateProperty(
                'C__CATS__CP_CONTRACT__END_DATE',
                'LC__CMDB__CATS_CP_CONTRACT__END_DATE',
                'isys_catg_sim_card_list__end_date',
                'isys_catg_sim_card_list'
            ),
            'threshold_date'   => new DateProperty(
                'C__CATS__CP_CONTRACT__THRESHOLD',
                'LC__CMDB__CATS_CP_CONTRACT__THRESHOLD',
                'isys_catg_sim_card_list__threshold_date',
                'isys_catg_sim_card_list',
            ),
            'card_no'          => new TextProperty(
                'C__CATS__CP_CONTRACT__CARD_NUMBER',
                'LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER',
                'isys_catg_sim_card_list__card_number',
                'isys_catg_sim_card_list'
            ),
            'phone_no'          => new TextProperty(
                'C__CATS__CP_CONTRACT__PHONE_NUMBER',
                'LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER',
                'isys_catg_sim_card_list__phone_number',
                'isys_catg_sim_card_list'
            ),
            'client_no'          => new TextProperty(
                'C__CATS__CP_CONTRACT__CLIENT_NUMBER',
                'LC__CMDB__CATS_CP_CONTRACT__CLIENT_NUMBER',
                'isys_catg_sim_card_list__client_number',
                'isys_catg_sim_card_list'
            ),
            'pin'          => new TextProperty(
                'C__CATS__CP_CONTRACT__PIN',
                'LC__CMDB__CATS_CP_CONTRACT__PIN',
                'isys_catg_sim_card_list__pin',
                'isys_catg_sim_card_list'
            ),
            'pin2'          => new TextProperty(
                'C__CATS__CP_CONTRACT__PIN2',
                'LC__CMDB__CATS_CP_CONTRACT__PIN2',
                'isys_catg_sim_card_list__pin2',
                'isys_catg_sim_card_list'
            ),
            'puk'          => new TextProperty(
                'C__CATS__CP_CONTRACT__PUK',
                'LC__CMDB__CATS_CP_CONTRACT__PUK',
                'isys_catg_sim_card_list__puk',
                'isys_catg_sim_card_list'
            ),
            'puk2'          => new TextProperty(
                'C__CATS__CP_CONTRACT__PUK2',
                'LC__CMDB__CATS_CP_CONTRACT__PUK2',
                'isys_catg_sim_card_list__puk2',
                'isys_catg_sim_card_list'
            ),
            'serial'          => new TextProperty(
                'C__CATS__CP_CONTRACT__SERIAL_NUMBER',
                'LC__CMDB__CATG__SERIAL',
                'isys_catg_sim_card_list__serial_number',
                'isys_catg_sim_card_list'
            ),
            'twincard' => new DialogYesNoProperty(
                'C__CMDB__CATG__SIM_CARD__TWINCARD',
                'LC__CMDB__CATS_CP_CONTRACT__TWINCARD',
                'isys_catg_sim_card_list__twincard',
                'isys_catg_sim_card_list',
                0
            ),
            'tc_card_no'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_CARD_NUMBER',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__CARD_NUMBER (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_card_number',
                'isys_catg_sim_card_list'
            ),
            'tc_phone_no'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_PHONE_NUMBER',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__PHONE_NUMBER (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_phone_number',
                'isys_catg_sim_card_list'
            ),
            'tc_pin'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_PIN',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__PIN (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_pin',
                'isys_catg_sim_card_list'
            ),
            'tc_pin2'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_PIN2',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__PIN2 (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_pin2',
                'isys_catg_sim_card_list'
            ),
            'tc_puk'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_PUK',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__PUK (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_puk',
                'isys_catg_sim_card_list'
            ),
            'tc_puk2'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_PUK2',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__PUK2 (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_puk2',
                'isys_catg_sim_card_list'
            ),
            'tc_serial_no'          => new TextProperty(
                'C__CATS__CP_CONTRACT__TC_SERIAL_NUMBER',
                $language->get_in_text('LC__CMDB__CATG__SERIAL (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__tc_serial_number',
                'isys_catg_sim_card_list'
            ),
            'optional_info'    => new TextAreaProperty(
                'C__CATS__CP_CONTRACT__TC_DESCRIPTION',
                $language->get_in_text('LC__CMDB__CATS_CP_CONTRACT__OPTIONAL_INFO (LC__CMDB__CATS_CP_CONTRACT__TWINCARD)'),
                'isys_catg_sim_card_list__optional_info',
                'isys_catg_sim_card_list'
            ),
            'description'      => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . $this->m_cat_type . $this->m_category_id,
                'isys_catg_sim_card_list__description',
                'isys_catg_sim_card_list'
            )
        ];
    }

    public function rank_record($entryId, $p_direction, $p_table, $p_checkMethod = null, $p_purge = false)
    {
        if ($p_purge) {
            // Please note that the first '?:' is necessary, because '$this->get_object_id()' will return an integer (not null).
            $objectId = $this->get_object_id() ?: $_GET[C__CMDB__GET__OBJECT] ?? $this->get_data($entryId)->get_row_value('isys_catg_sim_card_list__isys_obj__id');

            // @see ID-11629 Use 'check_rights_obj_and_category' instead of 'has_rights_in_obj_and_category' to actively throw an exception.
            isys_module_cmdb::getAuth()->check_rights_obj_and_category(isys_auth::SUPERVISOR, $objectId, $this->get_category_const());

            $l_dao_relation = isys_cmdb_dao_category_g_relation::instance($this->get_database_component());

            $l_sql = 'SELECT  isys_catg_assigned_cards_list__isys_catg_relation_list__id
                FROM isys_catg_assigned_cards_list
                INNER JOIN  isys_catg_sim_card_list ON isys_catg_assigned_cards_list__isys_obj__id__card =  isys_catg_sim_card_list__isys_obj__id
                WHERE isys_catg_sim_card_list__id = ' . $this->convert_sql_id($entryId) . '
                LIMIT 1;';

            $l_relation_id = $this->retrieve($l_sql)->get_row_value('isys_catg_assigned_cards_list__isys_catg_relation_list__id');

            // Delete relation.
            if ($l_relation_id > 0) {
                $l_dao_relation->delete_relation($l_relation_id);
            }
        }

        return parent::rank_record($entryId, $p_direction, $p_table, $p_checkMethod, $p_purge);
    }

    /**
     * @param int|int[] $ids
     * @param string    $tag
     * @param false     $asId
     *
     * @return CollectionInterface
     * @throws Exception
     */
    public function getAttachedEntries($ids, $tag = '', $asId = false): CollectionInterface
    {
        $collection = new ObjectCollection();

        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return $collection;
        }

        $query = 'SELECT
            isys_catg_assigned_cards_list__id as id,
            isys_catg_assigned_cards_list__isys_obj__id as objId,
            isys_obj__title as title,
            isys_obj__sysid as sysid,
            isys_obj_type__title as objType
            FROM isys_catg_assigned_cards_list
                INNER JOIN isys_obj ON isys_obj__id = isys_catg_assigned_cards_list__isys_obj__id
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_assigned_cards_list__isys_obj__id__card IN (' . implode(',', array_map(function ($id) {
            return $this->convert_sql_id($id);
        }, $ids)). ')';

        $result = $this->retrieve($query);

        while ($row = $result->get_row()) {
            $collection->addEntry(ObjectEntry::factory(
                $row['objId'],
                $row['title'],
                $row['sysid'],
                isys_application::instance()->container->get('language')->get($row['objType']),
                $row
            ));
        }

        return $collection;
    }
}
