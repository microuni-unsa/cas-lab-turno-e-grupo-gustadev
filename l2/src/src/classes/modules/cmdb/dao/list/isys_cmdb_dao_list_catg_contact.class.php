<?php

/**
 * i-doit
 *
 * DAO: Category list for contacts.
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      André Wösten <awoesten@i-doit.org>
 * @version     Dennis Blümer
 * @version     Van Quyen Hoang
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_catg_contact extends isys_component_dao_category_table_list
{
    /**
     * Counter for the dialog smarty-plugin.
     *
     * @var  integer
     */
    protected $m_i = 0;

    /**
     * Return constant of category.
     *
     * @return  integer
     * @author  Niclas Potthast <npotthast@i-doit.org>
     */
    public function get_category()
    {
        return defined_or_default('C__CATG__CONTACT');
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
     * Method for retrieving the category-data.
     *
     * @param   mixed   $p_unused
     * @param   integer $p_objID
     * @param   integer $p_cRecStatus
     *
     * @return  isys_component_dao_result
     */
    public function get_result($p_unused = null, $p_objID = null, $p_cRecStatus = null)
    {
        $l_cRecStatus = empty($p_cRecStatus) ? $this->get_rec_status() : $p_cRecStatus;

        $l_sql = "SELECT *, isys_catg_mail_addresses_list__title AS mail_address
			FROM isys_catg_contact_list
			INNER JOIN isys_connection ON isys_catg_contact_list__isys_connection__id = isys_connection__id
			LEFT JOIN isys_obj ON isys_connection__isys_obj__id = isys_obj__id
			LEFT JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_cats_person_group_list ON isys_cats_person_group_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_cats_organization_list ON isys_cats_organization_list__isys_obj__id = isys_connection__isys_obj__id
			LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_connection__isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
			WHERE TRUE ";

        if (!empty($p_objID)) {
            $l_sql .= "AND isys_catg_contact_list__isys_obj__id = " . $this->convert_sql_id($p_objID);
        }

        if (!empty($l_cRecStatus)) {
            $l_sql .= " AND isys_catg_contact_list__status = " . $this->convert_sql_id($l_cRecStatus);
        }

        return $this->retrieve($l_sql . " GROUP BY isys_catg_contact_list__id;");
    }

    /**
     *
     * @param   array &$p_arrRow
     *
     * @return  array
     */
    public function modify_row(&$p_arrRow)
    {
        global $g_dirs, $g_config;

        $l_empty_value = isys_tenantsettings::get('gui.empty_value', '-');
        $l_dao = isys_application::instance()->container->get('cmdb_dao');
        $language = isys_application::instance()->container->get('language');
        $routeGenerator = isys_application::instance()->container->get('route_generator');
        $quickinfo = isys_ajax_handler_quick_info::instance();

        // Prevent selection of archivied or deleted assignements as primary.
        if ($_SESSION['cRecStatusListView'] == C__RECORD_STATUS__NORMAL) {
            if (isys_auth_cmdb::instance()
                ->has_rights_in_obj_and_category(isys_auth::EDIT, $p_arrRow['isys_catg_contact_list__isys_obj__id'], 'C__CATG__CONTACT')) {
                $l_onclick = "window.toggle_primary_contact(this, " . ((int)$p_arrRow['isys_catg_contact_list__id']) . ", " .
                    ((int)$p_arrRow['isys_catg_contact_list__isys_obj__id']) . ");";

                if ($p_arrRow['isys_catg_contact_list__primary_contact'] > 0) {
                    $p_arrRow['contact_primary'] = '<button class="btn primary-button text-green" type="button" onclick="' . $l_onclick . '" title="' . $language->get('LC__CATG__CONTACT_LIST__MARK_AS_PRIMARY') . '" data-tooltip="1">
						<img src="' . $g_dirs['images'] . 'axialis/basic/symbol-ok.svg" />
						<span>' . $language->get('LC__UNIVERSAL__YES') . '</span>
						</button>';
                } else {
                    $p_arrRow['contact_primary'] = '<button class="btn primary-button text-red" type="button" onclick="' . $l_onclick . '" title="' . $language->get('LC__CATG__CONTACT_LIST__MARK_AS_PRIMARY') . '" data-tooltip="1">
						<img src="' . $g_dirs['images'] . 'axialis/basic/symbol-cancel.svg" />
						<span>' . $language->get('LC__UNIVERSAL__NO') . '</span>
						</button>';
                }

                // Wrap the button, so that "changing" the state does not make the table bouncy.
                $p_arrRow['contact_primary'] = '<div style="width:75px;">' . $p_arrRow['contact_primary'] . '</div>';
            } elseif ($p_arrRow['isys_catg_contact_list__primary_contact'] > 0) {
                $p_arrRow['contact_primary'] = '<img class="vam mr5" src="' . $g_dirs['images'] . 'axialis/basic/symbol-ok.svg" /><span class="vam">' .
                    $language->get('LC__UNIVERSAL__YES') . '</span>';
            } else {
                $p_arrRow['contact_primary'] = '<img class="vam mr5" src="' . $g_dirs['images'] . 'axialis/basic/symbol-cancel.svg" /><span class="vam">' .
                    $language->get('LC__UNIVERSAL__NO') . '</span>';
            }
        } else {
            $p_arrRow['contact_primary'] = $l_empty_value;
        }

        $changeRoleUrl = $routeGenerator->generate('cmdb.category.contact.change-role');
        $objectId = (int)$_GET[C__CMDB__GET__OBJECT];
        $entryId = (int)$p_arrRow['isys_catg_contact_list__id'];

        $l_params = [
            'p_strPopupType'    => 'dialog_plus',
            'p_strSelectedID'   => $p_arrRow['isys_catg_contact_list__isys_contact_tag__id'],
            'p_strTable'        => 'isys_contact_tag',
            'p_strClass'        => 'input-block',
            'p_bInfoIconSpacer' => 0,
            'name'              => 'C__CATG__CONTACT_TAG_' . $this->m_i++,
            'p_onChange'        => "new Ajax.Request('{$changeRoleUrl}', { " .
                "method: 'post', ".
                "parameters: { 'object-id': {$objectId}, 'entry-id': {$entryId}, 'role-id': this.value }, " .
                "onComplete: (xhr) => { if (is_json_response(xhr, true)) { idoit.Notify.success(xhr.responseJSON.message, { life: 5 }); } }" .
                "});",
            'p_bEditMode'       => true
        ];

        $l_obj_type_arr = $l_dao->get_objtype($p_arrRow['isys_obj__isys_obj_type__id'])
            ->get_row();

        $iconUrl = $routeGenerator->generate('cmdb.object-type.icon', ['objectTypeId' => $p_arrRow['isys_obj__isys_obj_type__id']]);

        $p_arrRow['contact_type'] = '<div class="display-flex align-items-center">' .
            '<img src="' . $iconUrl . '" class="mr5" title="' . $language->get($l_obj_type_arr['isys_obj_type__title']) . '" />' .
            '<span>' . $language->get($l_obj_type_arr['isys_obj_type__title']) . '</span>' .
            '</div>';

        $p_arrRow['contact_tag'] = (new isys_smarty_plugin_f_popup)->set_parameter($l_params);

        $p_arrRow['contact_mail'] = $p_arrRow['mail_address'];

        $p_arrRow['contact_name'] = $p_arrRow['isys_obj__title'];

        if ($p_arrRow['isys_cats_person_list__id']) {
            $p_arrRow['contact_department'] = $p_arrRow['isys_cats_person_list__department'];

            if (!empty($p_arrRow['isys_cats_person_list__first_name']) && !empty($p_arrRow['isys_cats_person_list__last_name'])) {
                $p_arrRow['contact_name'] = $p_arrRow['isys_cats_person_list__first_name'] . ' ' . $p_arrRow['isys_cats_person_list__last_name'];
            }

            $phone = $l_empty_value;

            if (!empty($p_arrRow['isys_cats_person_list__phone_company'])) {
                $phone = $language->get('LC__CONTACT__PERSON_TELEPHONE_COMPANY') . ': <strong>' . $p_arrRow['isys_cats_person_list__phone_company'] . '</strong>';
            } elseif (!empty($p_arrRow['isys_cats_person_list__phone_mobile'])) {
                $phone = $language->get('LC__CONTACT__PERSON_TELEPHONE_MOBILE') . ': <strong>' . $p_arrRow['isys_cats_person_list__phone_mobile'] . '</strong>';
            } elseif (!empty($p_arrRow['isys_cats_person_list__phone_home'])) {
                $phone = $language->get('LC__CONTACT__PERSON_TELEPHONE_HOME') . ': <strong>' . $p_arrRow['isys_cats_person_list__phone_home'] . '</strong>';
            }

            $p_arrRow['contact_telephone'] = $phone;

            if ($p_arrRow['isys_cats_person_list__isys_connection__id'] > 0) {
                $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
                $l_row = $l_dao->get_connection($p_arrRow['isys_cats_person_list__isys_connection__id'])
                    ->get_row();

                if ($l_row['isys_connection__isys_obj__id']) {
                    $p_arrRow['contact_organization'] = $quickinfo->getQuickInfoReplacement(
                        $l_row['isys_connection__isys_obj__id'],
                        $l_dao->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id'])
                    );
                } else {
                    $p_arrRow['contact_organization'] = $language->get('LC__CATG__CONTACT_LIST__NO_ORGANISATION_ASSIGNED');
                }
            } else {
                $p_arrRow['contact_organization'] = $language->get('LC__CATG__CONTACT_LIST__NO_ORGANISATION_ASSIGNED');
            }
        } elseif ($p_arrRow['isys_cats_person_group_list__id']) {
            if (!empty($p_arrRow['isys_cats_person_group_list__title'])) {
                $p_arrRow['contact_name'] = $p_arrRow['isys_cats_person_group_list__title'];
            }

            $p_arrRow['contact_telephone'] = $p_arrRow['isys_cats_person_group_list__phone'];
            $p_arrRow['contact_organization'] = $l_empty_value;
        } elseif ($p_arrRow['isys_cats_organization_list__id']) {
            if (!empty($p_arrRow['isys_cats_organization_list__title'])) {
                $p_arrRow['contact_name'] = $p_arrRow['isys_cats_organization_list__title'];
            }

            $p_arrRow['contact_telephone'] = $p_arrRow['isys_cats_organization_list__telephone'];

            if ($p_arrRow['isys_cats_organization_list__isys_connection__id'] > 0) {
                $l_dao = new isys_cmdb_dao_connection($this->get_database_component());
                $l_row = $l_dao->get_connection($p_arrRow['isys_cats_organization_list__isys_connection__id'])
                    ->get_row();

                $p_arrRow['contact_organization'] = $l_row['isys_connection__isys_obj__id'] ? $quickinfo->getQuickInfoReplacement(
                    $l_row['isys_connection__isys_obj__id'],
                    $l_dao->get_obj_name_by_id_as_string($l_row['isys_connection__isys_obj__id'])
                ) : $language->get('LC__CATG__CONTACT_LIST__NO_ORGANISATION_ASSIGNED');
            } else {
                $p_arrRow['contact_organization'] = $language->get('LC__CATG__CONTACT_LIST__NO_ORGANISATION_ASSIGNED');
            }
        }

        $p_arrRow['contact_name'] = $quickinfo->getQuickInfoReplacement(
            $p_arrRow['isys_obj__id'],
            $p_arrRow['contact_name']
        );
    }

    /**
     * Method for retrieving the table fields.
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'contact_name'         => 'LC__CATG__CONTACT_LIST__NAME',
            'contact_type'         => 'LC__CATG__CONTACT_LIST__TYPE',
            'contact_department'   => 'LC__CONTACT__PERSON_DEPARTMENT',
            'contact_mail'         => 'LC__CONTACT__PERSON_MAIL_ADDRESS',
            'contact_telephone'    => 'LC__CATG__CONTACT_LIST__PHONE',
            'contact_organization' => 'LC__CATG__CONTACT_LIST__ASSIGNED_ORGANISATION',
            'contact_tag'          => 'LC__CMDB__CONTACT_ROLE',
            'contact_primary'      => 'LC__CATG__CONTACT_LIST__PRIMARY'
        ];
    }

    /**
     * Returns an array of properties, that should not be available to be filtered.
     *
     * @return array
     * @see ID-9761
     */
    public function getUnfilterableProps(): array
    {
        return [
            'contact_tag'
        ];
    }
}
