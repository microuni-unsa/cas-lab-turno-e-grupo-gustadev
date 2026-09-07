<?php

/**
 * i-doit
 *
 * DAO: ObjectType lists.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Dennis Stuecken <dstuecken@synetics.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_file extends isys_component_dao_category_table_list
{
    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__FILE');
    }

    /**
     * Return constant of category type.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_GLOBAL;
    }

    /**
     *
     * @param string  $p_table
     * @param integer $p_objID
     * @param null    $p_cRecStatus
     *
     * @return   isys_component_dao_result
     */
    public function get_result($p_table = null, $p_objID = null, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_strSQL = "SELECT
			isys_catg_file_list__id,
			isys_catg_file_list__link,
            isys_catg_file_list__description,
			isys_file_version__isys_file_physical__id,
			isys_file_version__revision,
			isys_file_category__title,
			isys_obj__id,
			isys_obj__title,
			isys_obj_type__title,
			isys_catg_file_list__status
			FROM isys_catg_file_list
			LEFT JOIN isys_connection ON isys_connection__id = isys_catg_file_list__isys_connection__id
			LEFT JOIN isys_cats_file_list ON isys_cats_file_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_file_version ON isys_cats_file_list__isys_file_version__id=isys_file_version__id
			LEFT JOIN isys_obj ON isys_file_version__isys_obj__id = isys_obj__id
			LEFT JOIN isys_obj_type ON isys_obj__isys_obj_type__id = isys_obj_type__id
			LEFT JOIN isys_file_category ON isys_file_category__id = isys_cats_file_list__isys_file_category__id
			WHERE isys_catg_file_list__isys_obj__id = " . $this->convert_sql_id($p_objID) . "
			AND isys_catg_file_list__status = " . $this->convert_sql_int($l_cRecStatus) . "
			GROUP BY isys_catg_file_list__id";

        return $this->retrieve($l_strSQL);
    }

    /**
     * @param array $p_row
     *
     * @author  Dennis Stuecken <dstuecken@synetics.de>
     */
    public function modify_row(&$p_row)
    {
        $linkIcon = isys_application::instance()->www_path . 'images/axialis/basic/link.svg';
        $emptyValue = isys_tenantsettings::get('gui.empty_value', '-');
        $language = isys_application::instance()->container->get('language');

        if ($p_row["isys_file_version__isys_file_physical__id"] !== null) {
            $allowedToView = true;

            if (isys_tenantsettings::get('auth.use-in-file-browser', false)) {
                $allowedToView = isys_auth_cmdb::instance()
                    ->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $p_row["isys_obj__id"]);
            }

            $p_row["download_file_name"] = $language->get($p_row['isys_obj_type__title']) . ' > ' . $p_row['isys_obj__title'];

            if ($allowedToView) {
                $downloadUrl = isys_application::instance()->container->get('route_generator')
                    ->generate('system.file-download', ['type' => 'cmdb.global-file-category', 'identifier' => $p_row["isys_file_version__isys_file_physical__id"]]);

                // ID-2223  Adding "event.stopPropagation();" stops the browser from opening the category itself.
                $p_row["download"] = '<a target="_blank" class="btn btn-small" href="' . $downloadUrl . '" onclick="event.stopPropagation();">' .
                    '<img src="' . $linkIcon . '" class="mr5" alt="" />' .
                    '<span>' . $language->get('LC__UNIVERSAL__DOWNLOAD_FILE') . '</span>' .
                    '</a>';
            } else {
                $p_row["download"] = $emptyValue;
            }
        } else {
            $p_row["download_file_name"] = $emptyValue;
            $p_row["download"] = $emptyValue;
        }

        if (!empty($p_row["isys_catg_file_list__link"])) {
            $p_row["isys_catg_file_list__link"] = isys_helper_link::create_anker(
                htmlentities($p_row["isys_catg_file_list__link"]),
                '_blank',
                '<img src="' . $linkIcon . '" class="vam" alt="" />&nbsp;'
            );
        } else {
            $p_row["isys_catg_file_list__link"] = $emptyValue;
        }
    }

    /**
     * Method for retrieving the category list header.
     *
     * @return array
     */
    public function get_fields()
    {
        return [
            'download_file_name'               => 'LC__CMDB__CATG__FILE_OBJ_FILE',
            'isys_file_version__revision'      => 'LC_UNIVERSAL__REVISION',
            'isys_catg_file_list__link'        => 'Link',
            'isys_file_category__title'        => 'LC__CMDB__CATS__FILE_CATEGORY',
            'download'                         => 'LC__CMDB__CATS__FILE_DOWNLOAD',
            'isys_catg_file_list__description' => 'LC__CMDB__CAT__COMMENTARY',
        ];
    }
}
