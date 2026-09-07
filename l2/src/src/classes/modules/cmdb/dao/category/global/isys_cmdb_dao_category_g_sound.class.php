<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 * DAO: global category for sound cards
 *
 * @package    i-doit
 * @subpackage CMDB_Categories
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_sound extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'sound';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__SOUND';
    }

    /**
     * @inheritDoc
     */
    public function get_data($entryId = null, $objectId = null, $condition = '', $filter = null, $status = null)
    {
        $l_sql = 'SELECT * FROM isys_catg_sound_list
			INNER JOIN isys_obj ON isys_catg_sound_list__isys_obj__id = isys_obj__id
			LEFT JOIN isys_sound_manufacturer ON isys_sound_manufacturer__id = isys_catg_sound_list__isys_sound_manufacturer__id
			WHERE TRUE ' . $condition . ' ' . $this->prepare_filter($filter) . ' ';

        if ($objectId !== null) {
            $l_sql .= $this->get_object_condition($objectId);
        }

        if ($entryId !== null) {
            $l_sql .= ' AND isys_catg_sound_list__id = ' . $this->convert_sql_id($entryId);
        }

        if ($status !== null) {
            $l_sql .= ' AND isys_catg_sound_list__status = ' . $this->convert_sql_int($status);
        }

        return $this->retrieve($l_sql . ';');
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        return [
            'manufacturer' => new DialogPlusProperty(
                'C__CATG__SOUND__MANUFACTURER',
                'LC__CMDB__CATG__MANUFACTURE',
                'isys_catg_sound_list__isys_sound_manufacturer__id',
                'isys_catg_sound_list',
                'isys_sound_manufacturer',
            ),
            'title'        => new TextProperty(
                'C__CATG__SOUND__TITLE',
                'LC__CMDB__CATG__TITLE',
                'isys_catg_sound_list__title',
                'isys_catg_sound_list'
            ),
            'description'  => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . $this->m_cat_type . $this->m_category_id,
                'isys_catg_sound_list__description',
                'isys_catg_sound_list'
            )
        ];
    }

    /**
     * Import-Handler for this category
     */
    public function import($p_data)
    {
        $l_ids = [];
        $this->get_general_data();

        if (is_countable($p_data) && count($p_data) > 0) {
            // Iterate through Graphic-Adapters.
            foreach ($p_data as $l_entry) {
                $l_ids[] = $this->create_data([
                    'title'        => $l_entry["name"],
                    'manufacturer' => isys_import::check_dialog("isys_sound_manufacturer", $l_entry["manufacturer"]),
                ]);
            }
        }

        return $l_ids;
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param array $p_data
     * @return array
     */
    public function parse_import_array(array $p_data = [])
    {
        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_sound_manufacturer', $p_data['manufacturer']);
        } else {
            $l_manufacturer = null;
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'title'        => [
                    'value' => $p_data['title'],
                ],
                'manufacturer' => [
                    'value' => $l_manufacturer,
                ],
                'description'  => [
                    'value' => $p_data['description'],
                ],
            ],
        ];
    }
}
