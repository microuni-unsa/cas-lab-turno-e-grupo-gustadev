<?php

use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogPlusDependencyChildProperty;
use idoit\Component\Property\Type\DialogPlusDependencyParentProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for models.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_model extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        // @see ID-11126 This needs to be set BEFORE calling the parent constructor.
        $this->m_category = 'model';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__MODEL';
        $this->m_is_purgable = true;
    }

    /**
     * Callback method to get the model manufacturer id.
     *
     * @param   isys_request $p_request
     *
     * @return  mixed
     */
    public function callback_property_title_ui_params_secTableID(isys_request $p_request)
    {
        $l_cat_id = $p_request->get_category_data_id();
        $l_obj_id = $p_request->get_object_id();

        if ($l_cat_id > 0) {
            return $this->get_data($l_cat_id)
                ->get_row_value('isys_catg_model_list__isys_model_manufacturer__id');
        } elseif ($l_obj_id > 0) {
            return $this->get_data(null, $l_obj_id)
                ->get_row_value('isys_catg_model_list__isys_model_manufacturer__id');
        } else {
            return -1;
        }
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
            'manufacturer' => new DialogPlusDependencyParentProperty(
                'C__CATG__MODEL_MANUFACTURER',
                'LC__CATG__STORAGE_MANUFACTURER',
                'isys_catg_model_list__isys_model_manufacturer__id',
                'isys_catg_model_list',
                'isys_model_manufacturer',
                'isys_model_title',
                'C__CATG__MODEL_TITLE_ID'
            ),
            'title' => new DialogPlusDependencyChildProperty(
                'C__CATG__MODEL_TITLE_ID',
                'LC__CMDB__CATG__MODEL',
                'isys_catg_model_list__isys_model_title__id',
                'isys_catg_model_list',
                'isys_model_title',
                'isys_model_manufacturer',
                'isys_cmdb_dao_category_g_model::manufacturer',
                'C__CATG__MODEL_MANUFACTURER',
                new isys_callback([
                    'isys_cmdb_dao_category_g_model',
                    'callback_property_title_ui_params_secTableID'
                ]),
                [
                    'isys_export_helper',
                    'model_title'
                ]
            ),
            'productid'    => new TextProperty(
                'C__CATG__MODEL_PRODUCTID',
                'LC__CMDB__CATG__MODEL_PRODUCTID',
                'isys_catg_model_list__productid',
                'isys_catg_model_list',
            ),
            'service_tag'    => new TextProperty(
                'C__CATG__MODEL_SERVICE_TAG',
                'LC__CMDB__CATG__MODEL_SERVICE_TAG',
                'isys_catg_model_list__service_tag',
                'isys_catg_model_list',
            ),
            'serial'    => (new TextProperty(
                'C__CATG__MODEL_SERIAL',
                'LC__CMDB__CATG__SERIAL',
                'isys_catg_model_list__serial',
                'isys_catg_model_list',
            ))->mergePropertyData([
                C__PROPERTY__DATA__INDEX => true
            ]),
            'firmware'    => new TextProperty(
                'C__CATG__MODEL_FIRMWARE',
                'LC__CMDB__CATG__FIRMWARE',
                'isys_catg_model_list__firmware',
                'isys_catg_model_list',
            ),
            'description'    => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . defined_or_default(C__CMDB__CATEGORY__TYPE_GLOBAL, 0) . defined_or_default('C__CATG__MODEL', 'C__CATG__MODEL'),
                'isys_catg_model_list__description',
                'isys_catg_model_list',
            )
        ];
    }

    /**
     * Import-Handler for this category
     */
    public function import($p_data)
    {
        if (is_array($p_data)) {
            $l_bios = $p_data["bios"] ?: null;
            $l_battery_data = $p_data["battery"] ?: null;
            $p_data = $p_data[0] ?: null;
        } else {
            $l_bios = $l_battery_data = null;
        }

        if (is_array($p_data)) {
            $l_object_id = $_GET[C__CMDB__GET__OBJECT];
            $l_list_id = $this->create_connector($this->get_table(), $l_object_id);

            if ($l_list_id > 0) {
                $l_battery = '';

                if ($l_battery_data) {
                    $l_battery = "\nBattery: " . $l_battery_data["name"] . " (" . $l_battery_data["description"] . ")";
                }

                // Manufacturer.
                $manufacturer = isys_import::check_dialog("isys_model_manufacturer", $p_data["manufacturer"]);

                // Title.
                $l_title = $p_data["model"];
                if (!empty($p_data["systemtype"])) {
                    $l_title .= " (" . $p_data["systemtype"] . ")";
                }

                // Save.
                $this->save_data($l_list_id, [
                    'manufacturer' => $manufacturer,
                    'title' => isys_import::check_dialog("isys_model_title", $l_title, null, $manufacturer),
                    'serial' => $l_bios["serialnumber"],
                    'firmware' => $p_data["firmware"] ?: trim($l_bios["name"]),
                    'description' => "System: " . $p_data["systemtype"] . "\n" .
                        "BIOS: " . trim($l_bios["manufacturer"]) . " (" . trim($l_bios["name"]) . ")" . $l_battery
                ]);
            }
        }

        return $l_list_id;
    }

    /**
     * Builds an array with minimal requirement for the sync function.
     *
     * @param array $p_data
     *
     * @return array
     */
    public function parse_import_array($p_data)
    {
        $l_title = $l_manufacturer = null;

        if (!empty($p_data['manufacturer'])) {
            $l_manufacturer = isys_import_handler::check_dialog('isys_model_manufacturer', $p_data['manufacturer']);
        }

        if (!empty($p_data['title'])) {
            $l_title = isys_import_handler::check_dialog('isys_model_title', $p_data['title'], null, $l_manufacturer);
        }

        return [
            'data_id'    => $p_data['data_id'],
            'properties' => [
                'manufacturer' => [
                    'value' => $l_manufacturer
                ],
                'title'        => [
                    'value' => $l_title
                ],
                'serial'       => [
                    'value' => $p_data['serial']
                ],
                'productid'    => [
                    'value' => $p_data['productid']
                ],
                'firmware'     => [
                    'value' => $p_data['firmware']
                ],
                'service_tag'  => [
                    'value' => $p_data['service_tag']
                ],
                'description'  => [
                    'value' => $p_data['description']
                ]
            ]
        ];
    }

    /**
     * Get serial number as string
     *
     * @param int $objectId
     *
     * @return string|null
     * @throws isys_exception_database
     * @author Paul Kolbovich <pkolbovich@i-doit.org>
     */
    public function getSerialNumberAsString(int $objectId): ?string
    {
        if (empty($objectId)) {
            return null;
        }

        $objectId = $this->convert_sql_id($objectId);

        $query = "SELECT isys_catg_model_list__serial
            FROM isys_catg_model_list
            WHERE isys_catg_model_list__isys_obj__id = {$objectId}
            LIMIT 1;";

        return $this->retrieve($query)->get_row_value('isys_catg_model_list__serial');
    }
}
