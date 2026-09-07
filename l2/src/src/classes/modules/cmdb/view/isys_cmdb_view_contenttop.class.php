<?php

use idoit\Component\Helper\Purify;
use idoit\Module\Qrcode\Component\QrCode;

/**
 * CMDB Category view
 *
 * @package     i-doit
 * @subpackage  CMDB_Views
 * @author      Andre Wösten <awoesten@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_view_contenttop extends isys_cmdb_view
{
    /**
     * Returns the ID for the category view.
     *
     * @return  integer
     */
    public function get_id()
    {
        return C__CMDB__VIEW__CATEGORY;
    }

    /**
     * Returns the mandatory parameters.
     *
     * @param  array &$l_gets
     */
    public function get_mandatory_parameters(&$l_gets)
    {
        $l_gets[C__CMDB__GET__OBJECT] = true;
    }

    /**
     * Returns the name of the view.
     *
     * @return  string
     */
    public function get_name()
    {
        return "";
    }

    /**
     * Method for setting the optional parameters.
     *
     * @param  array &$l_gets
     */
    public function get_optional_parameters(&$l_gets)
    {
    }

    /**
     * Returns the filepath of the "bottom" template.
     *
     * @return  null
     */
    public function get_template_bottom()
    {
        return null;
    }

    /**
     * Returns the filepath of the "top" template.
     *
     * @return  string
     */
    public function get_template_top()
    {
        return "content/top/main_objectdetail.tpl";
    }

    /**
     * Process category view and handles the "authorization-fail" view.
     *
     * @throws  isys_exception_cmdb
     * @throws  Exception|isys_exception_cmdb
     * @return  null
     */
    public function process()
    {
        try {
            $this->overview_process();
        } catch (isys_exception_auth $e) {
            throw new Exception($e->getMessage());
        } catch (isys_exception_cmdb $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

        return null;
    }

    /**
     * Function processing the object overview in  a category view.
     * Used from object browser from now on!
     *
     * @param   string $p_tomdest
     *
     * @return  boolean
     * @throws  isys_exception_cmdb
     */
    public function overview_process($p_tomdest = "tom.content.top")
    {
        $l_db = $this->get_module_request()
            ->get_database();
        $l_gets = $this->get_module_request()
            ->get_gets();

        $l_gets = Purify::castIntValues($l_gets);

        $l_tpl = $this->get_module_request()
            ->get_template();

        $l_gets = Purify::purifyParams($l_gets);

        $objectId = (int)$l_gets[C__CMDB__GET__OBJECT];

        /**
         * --------------------------------------------------------------------------
         * GLOBAL
         * --------------------------------------------------------------------------
         */
        $l_dao_global = new isys_cmdb_dao_category_g_global($l_db);
        $l_cat_data = $l_dao_global->get_data(null, $objectId)
            ->__to_array();
        $l_str_access = isys_tenantsettings::get('gui.empty_value', '-');
        $l_rules = [];

        $l_str_purpose = isys_application::instance()->container->get('language')
            ->get((string)$l_cat_data["isys_purpose__title"]);

        /**
         * --------------------------------------------------------------------------
         * Relations
         * --------------------------------------------------------------------------
         */
        $l_dao_relations = new isys_cmdb_dao_category_g_relation($l_db);
        $l_str_relations = sprintf(
            isys_application::instance()->container->get('language')
            ->get("LC__CMDB__RELATION_HEADER"),
            $l_dao_relations->count_relations($objectId, C__RELATION__IMPLICIT),
            $l_dao_relations->count_relations($objectId, C__RELATION__EXPLICIT)
        );

        /**
         * --------------------------------------------------------------------------
         * ACCESS
         * --------------------------------------------------------------------------
         */
        $l_dao_access = new isys_cmdb_dao_category_g_access($l_db);
        $l_temp = isys_helper_link::handle_url_variables($l_dao_access->get_url($objectId), $objectId);
        if (!empty($l_temp)) {
            $l_str_access = '<a target="_blank" href="' . $l_temp . '">' . $l_temp . '</a>';
        }

        /**
         * --------------------------------------------------------------------------
         * CONTACT
         * --------------------------------------------------------------------------
         */
        if ($_GET[C__CMDB__GET__OBJECT] > 0) {
            $l_cc_dao = new isys_cmdb_dao_category_g_contact($l_db);
            $l_cc_dao->set_object_id($_GET[C__CMDB__GET__OBJECT]);

            $l_primID = null;
            $l_primType = null;
            $l_contact_data = $l_cc_dao->contact_get_primary($l_primType, $l_primID);

            $l_str_contact = '<a href="' . isys_helper_link::create_url([C__CMDB__GET__OBJECT => $l_primID]) . '">' . $l_contact_data['isys_obj__title'] . '</a>';
        } else {
            $l_str_contact = '';
        }

        /**
         * --------------------------------------------------------------------------
         * LOCATION
         * --------------------------------------------------------------------------
         */
        $l_loc_popup = new isys_popup_browser_location();
        $l_loc_dao = new isys_cmdb_dao_category_g_location($l_db);
        $l_str_location = $l_loc_popup->format_selection($l_loc_dao->get_parent_id_by_object($_GET[C__CMDB__GET__OBJECT]));

        /**
         * --------------------------------------------------------------------------
         * RULE Assignments
         * --------------------------------------------------------------------------
         */
        $l_rules["C__CATG__TITLE"]["p_strValue"] = str_replace('\\', '&#92;', $l_cat_data["isys_obj__title"]); // Fix for ID-602 (Backslashes in Object Title)
        $l_rules["C__CATG__SYSID"]["p_strValue"] = isys_glob_str_stop($l_cat_data["isys_obj__sysid"], 50);
        $l_rules["C__CATG__PURPOSE"]["p_strValue"] = isys_glob_str_stop($l_str_purpose, 50);
        $l_rules["C__CATG__RELATIONS"]["p_strValue"] = $l_str_relations;
        $l_rules["C__CATG__LOCATION"]["p_strValue"] = $l_str_location;
        $l_rules["C__CATG__CONTACT"]["p_strValue"] = $l_str_contact;
        $l_rules["C__CATG__ACCESS"]["p_strValue"] = $l_str_access;

        $this->processBarcode($l_tpl, $objectId);

        // Send rules via TOM to template.
        $l_tpl
            ->assign('editMode', $l_tpl->editmode())
            ->smarty_tom_add_rules($p_tomdest, $l_rules);

        /**
         * --------------------------------------------------------------------------
         * Emit processContentTop (this is used by the monitoring modules to extend the header with a live host status information)
         * --------------------------------------------------------------------------
         */
        isys_component_signalcollection::get_instance()
            ->emit("mod.cmdb.processContentTop", $l_cat_data);

        return true;
    }

    /**
     * @param isys_component_template $template
     * @param int                     $objectId
     *
     * @return void
     * @throws Exception
     */
    private function processBarcode(isys_component_template $template, int $objectId): void
    {
        if (!class_exists(isys_module_qrcode::class) || !class_exists(QrCode::class)) {
            return;
        }

        $qrCodeData = (new QrCode())->getData($objectId);

        // Do not show barcodes, if the configuration forbids it.
        if (empty($qrCodeData) || !isys_tenantsettings::get('barcode.enabled', 1)) {
            return;
        }

        $routeGenerator = isys_application::instance()->container->get('route_generator');

        $barcodeType = isys_tenantsettings::get('barcode.type', 'qr');

        if ($barcodeType === 'qr') {
            $barcodeUrl = $routeGenerator->generate('qrcode.image.object', ['objectId' => $objectId]);
            $modalUrl = $routeGenerator->generate('qrcode.modal.object', ['objectId' => $objectId]);
        } else {
            $barcodeUrl = $routeGenerator->generate('barcode.image.object', ['objectId' => $objectId]);
            $modalUrl = $routeGenerator->generate('barcode.modal.object', ['objectId' => $objectId]);
        }

        $template
            ->assign('barcodeVisible', !empty($qrCodeData))
            ->assign('barcodeType', $barcodeType)
            ->assign('barcodeLinkType', $qrCodeData['link'] ?? '')
            ->assign('barcodeSysId', $qrCodeData['sysid'] ?? '')
            ->assign('barcodeUrl', $barcodeUrl)
            ->assign('barcodePopupUrl', $modalUrl);
    }
}
