<?php

/**
 * i-doit
 *
 * CMDB image category.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_ui_category_g_image extends isys_cmdb_ui_category_global
{
    /**
     * @param isys_cmdb_dao_category $p_cat
     *
     * @return void
     * @throws Exception
     */
    public function process(isys_cmdb_dao_category $p_cat)
    {
        $objectId = $p_cat->get_object_id() ?? $_GET[C__CMDB__GET__OBJECT];

        $l_navbar = isys_component_template_navbar::getInstance()
            ->set_save_mode('formsubmit')
            ->set_active(false, C__NAVBAR_BUTTON__PRINT);

        $l_posts = isys_module_request::get_instance()->get_posts();

        $l_catdata = $p_cat->get_general_data();

        $l_rules = [];

        $this->fill_formfields($p_cat, $l_rules, $l_catdata);

        $l_image_name = $l_catdata['isys_catg_image_list__image_link'];

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__DELETE) {
            $p_cat->delete($l_catdata['isys_catg_image_list__id']);

            $p_cat->delete(null, $l_image_name);

            unset($l_image_name);
        }

        if (!empty($l_image_name)) {
            $this->get_template_component()
                ->assign("imageName", $l_image_name)
                ->assign("downloadUrl", isys_application::instance()->container->get('route_generator')->generate('system.file-download', ['type' => 'cmdb.object-image', 'identifier' => $objectId]));

            $deleteConfirmation = isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__IMAGE_DELETE_CONFIRM');

            $l_navbar->add_onclick_prepend(C__NAVBAR_BUTTON__DELETE, "if (! confirm('{$deleteConfirmation}')) {return false;}")
                ->set_active(isys_module_cmdb::getAuth()->has_rights_in_obj_and_category(isys_auth::DELETE, $objectId, $p_cat->get_category_const()), C__NAVBAR_BUTTON__DELETE)
                ->set_visible(true, C__NAVBAR_BUTTON__DELETE);
        }

        // Apply rules.
        $this->get_template_component()
            ->assign('encType', 'multipart/form-data')
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules);
    }
}
