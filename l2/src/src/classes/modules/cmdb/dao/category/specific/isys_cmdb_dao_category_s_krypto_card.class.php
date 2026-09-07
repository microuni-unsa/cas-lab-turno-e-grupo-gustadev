<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DateProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: specific category for crypto cards.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_krypto_card extends isys_cmdb_dao_category_specific
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'krypto_card';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATS__KRYPTO_CARD';
        $this->m_is_purgable = true;
    }

    /**
     * @inheritDoc
     */
    public function get_data($entryId = null, $objectId = null, $condition = "", $filter = null, $status = null)
    {
        $l_sql = "SELECT *
			FROM isys_cats_krypto_card_list
			INNER JOIN isys_obj ON isys_obj__id = isys_cats_krypto_card_list__isys_obj__id
			LEFT JOIN isys_catg_assigned_cards_list ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_cats_krypto_card_list__isys_obj__id
			WHERE TRUE " . $condition . $this->prepare_filter($filter);

        if ($entryId !== null) {
            $l_sql .= " AND isys_cats_krypto_card_list__id = " . $this->convert_sql_id($entryId);
        }

        if ($objectId !== null) {
            $l_sql .= $this->get_object_condition($objectId);
        }

        if ($status !== null) {
            $l_sql .= " AND isys_cats_krypto_card_list__status = " . $this->convert_sql_int($status);
        }

        return $this->retrieve($l_sql);
    }

    /**
     * Method for returning the properties.
     *
     * @return array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'certificate_number'    => new TextProperty(
                'C__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER',
                'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_NUMBER',
                'isys_cats_krypto_card_list__certificate_number',
                'isys_cats_krypto_card_list',
            ),
            'certgate_card_number'  => new TextProperty(
                'C__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER',
                'LC__CMDB__CATS__KRYPTO_CARD__CERTGATE_CARD_NUMBER',
                'isys_cats_krypto_card_list__certgate_card_number',
                'isys_cats_krypto_card_list',
            ),
            'certificate_title'     => new TextProperty(
                'C__CATS__KRYPTO_CARD__CERTIFICATE_TITLE',
                'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_TITLE',
                'isys_cats_krypto_card_list__certificate_title',
                'isys_cats_krypto_card_list',
            ),
            'certificate_password'  => new TextProperty(
                'C__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD',
                'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_PASSWORD',
                'isys_cats_krypto_card_list__certificate_password',
                'isys_cats_krypto_card_list',
            ),
            'certificate_procedure' => new DateProperty(
                'C__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE',
                'LC__CMDB__CATS__KRYPTO_CARD__CERTIFICATE_PROCEDURE',
                'isys_cats_krypto_card_list__certificate_procedure',
                'isys_cats_krypto_card_list',
            ),
            'date_of_issue'         => new DateProperty(
                'C__CATS__KRYPTO_CARD__DATE_OF_ISSUE',
                'LC__CMDB__CATS__KRYPTO_CARD__DATE_OF_ISSUE',
                'isys_cats_krypto_card_list__date_of_issue',
                'isys_cats_krypto_card_list',
            ),
            'imei_number'           => new TextProperty(
                'C__CATS__KRYPTO_CARD__IMEI_NUMBER',
                'LC__CMDB__CATS__KRYPTO_CARD__IMEI_NUMBER',
                'isys_cats_krypto_card_list__imei_number',
                'isys_cats_krypto_card_list',
            ),
            'assigned_mobile'       => array_replace_recursive(isys_cmdb_dao_category_pattern::object_browser(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__INFO__DESCRIPTION => 'assigned mobile phone',
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_catg_assigned_cards_list__isys_obj__id',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT  CONCAT(isys_obj__title, \' {\', isys_obj__id, \'}\')
                            FROM isys_cats_krypto_card_list
                            INNER JOIN isys_catg_assigned_cards_list ON isys_catg_assigned_cards_list__isys_obj__id__card = isys_cats_krypto_card_list__isys_obj__id
                            INNER JOIN isys_obj ON isys_obj__id = isys_catg_assigned_cards_list__isys_obj__id',
                        'isys_cats_krypto_card_list',
                        'isys_cats_krypto_card_list__id',
                        'isys_cats_krypto_card_list__isys_obj__id',
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_cats_krypto_card_list',
                            'LEFT',
                            'isys_cats_krypto_card_list__isys_obj__id',
                            'isys_obj__id',
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_catg_assigned_cards_list',
                            'LEFT',
                            'isys_cats_krypto_card_list__isys_obj__id',
                            'isys_catg_assigned_cards_list__isys_obj__id__card',
                        ),
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory(
                            'isys_obj',
                            'LEFT',
                            'isys_catg_assigned_cards_list__isys_obj__id',
                            'isys_obj__id',
                        ),
                    ],
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CATS__KRYPTO_CARD__ASSIGNED_MOBILE_PHONE',
                    C__PROPERTY__UI__PARAMS => [
                        'catFilter'   => "C__CATS__CELL_PHONE_CONTRACT",
                        'p_bReadonly' => "1",
                    ],
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'object',
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                ],
            ]),
            'description'           => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . $this->m_cat_type . $this->m_category_id,
                'isys_cats_krypto_card_list__description',
                'isys_cats_krypto_card_list',
            ),
        ];
    }

    /**
     * @param array $data
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function create_data($data)
    {
        // Set the value or '-1' if nothing was passed.
        $assignedMobile = (int)($data['assigned_mobile'] ?? -1);
        // Unset this property, because it needs special handling.
        unset($data['assigned_mobile']);

        $entryId = parent::create_data($data);

        if (!$entryId) {
            return false;
        }

        $this->assignMobilePhone((int)$entryId, $assignedMobile);

        return true;
    }

    /**
     * @param int $entryId
     * @param array $data
     * @return bool
     * @throws isys_exception_dao
     * @throws isys_exception_dao_cmdb
     * @throws isys_exception_database
     */
    public function save_data($entryId, $data)
    {
        // Set the value or '-1' if nothing was passed.
        $assignedMobile = (int)($data['assigned_mobile'] ?? -1);
        // Unset this property, because it needs special handling.
        unset($data['assigned_mobile']);

        if (!parent::save_data($entryId, $data)) {
            return false;
        }

        $this->assignMobilePhone((int)$entryId, $assignedMobile);

        return true;
    }

    /**
     * @param int $entryId
     * @param int $objectId
     * @return void
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function assignMobilePhone(int $entryId, int $objectId): void
    {
        if ($objectId < 0) {
            return;
        }

        $card = $this->get_data($entryId)->get_row();

        if ($card['isys_cats_krypto_card_list__isys_obj__id'] > 0) {
            $cardDao = isys_cmdb_dao_category_g_assigned_cards::instance($this->get_database_component());
            $cardDao->remove_component(null, $card['isys_cats_krypto_card_list__isys_obj__id']);

            if ($objectId > 0) {
                $cardDao->add_component($objectId, $card['isys_cats_krypto_card_list__isys_obj__id']);
            }
        }
    }
}
