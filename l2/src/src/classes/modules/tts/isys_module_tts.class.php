<?php

use idoit\Component\FeatureManager\FeatureManager;

/**
 * i-doit
 *
 * Trouble-Ticket-System Module
 *
 * @package    i-doit
 * @subpackage Modules
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_tts extends isys_module implements isys_module_interface
{
    /** @var isys_component_template_language_manager */
    protected $language;

    /**
     * Module constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->language = isys_application::instance()->container->get('language');
    }

    /**
     * Method for retrieving the breadcrumb part.
     *
     * @param array $gets
     *
     * @return array|null
     */
    public function breadcrumb_get(&$gets)
    {
        if (!FeatureManager::isFeatureActive('tts-category')) {
            return null;
        }

        return [
            [
                $this->language->get('LC__MODULE__SYSTEM__TREE__INTERFACES') => [
                    C__GET__MODULE_ID => C__MODULE__SYSTEM,
                    C__GET__MODULE_SUB_ID => C__MODULE__TTS
                ]
            ],
            [
                $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__TTS') => null
            ]
        ];
    }

    /**
     * @param isys_component_tree $tree
     * @param int                 $interfacesNode
     *
     * @return void
     * @throws Exception
     */
    public static function extendInterfacesTree(isys_component_tree $tree, int $interfacesNode)
    {
        if (!defined('C__MODULE__TTS') || !FeatureManager::isFeatureActive('tts-settings')) {
            return;
        }

        $authSystem = isys_module_system::getAuth();

        if (!$authSystem->is_allowed_to(isys_auth::VIEW, 'TTS')) {
            return;
        }

        $nextId = $tree->count();
        $language = isys_application::instance()->container->get('language');
        $imageDir = isys_application::instance()->www_path . 'images/axialis/';

        $tree->add_node(
            ++$nextId,
            $interfacesNode,
            $language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__TTS'),
            isys_helper_link::create_url([
                C__GET__MODULE_ID     => C__MODULE__SYSTEM,
                C__GET__MODULE_SUB_ID => C__MODULE__TTS,
            ]),
            '',
            "{$imageDir}basic/comment-empty.svg",
            $_GET[C__GET__MODULE_ID] == C__MODULE__SYSTEM && $_GET[C__GET__MODULE_SUB_ID] == C__MODULE__TTS,
            null,
            null,
            $authSystem->is_allowed_to(isys_auth::VIEW, 'TTS/CONFIG')
        );
    }

    /**
     * Start module
     */
    public function start()
    {
        if (!FeatureManager::isFeatureActive('tts-settings')) {
            header('Location: ?');
            die;
        }

        isys_module_system::getAuth()->check(isys_auth::VIEW, 'TTS/CONFIG');

        $l_dao_tts = new isys_tts_dao(isys_application::instance()->container->get('database'));

        $l_posts = isys_module_request::get_instance()->get_posts();

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__SAVE) {
            if (strpos($l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"], "://") !== false) {
                $l_password = $l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__PASS"];

                if ($l_posts['C__MODULE__REQUEST_TRACKER_CONFIG__PASS__action'] == isys_smarty_plugin_f_password::PASSWORD_UNCHANGED) {
                    $l_password = null;
                }

                $l_dao_tts->save(
                    (bool)$l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE"],
                    (int)$l_posts["C__TTS__TYPE"],
                    $l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__LINK"],
                    $l_posts["C__MODULE__REQUEST_TRACKER_CONFIG__USER"],
                    $l_password,
                    (int)$l_posts['C__MODULE__REQUEST_TRACKER_CONFIG__TLS_CERTIFICATE__HIDDEN']
                );

                // Moved here from system settings.
                isys_application::instance()->container->get('settingsTenant')->set('tts.rt.queues', $l_posts['C__MODULE__REQUEST_TRACKER_CONFIG__RT_QUEUES']);

                isys_notify::success($this->language->get('LC__INFOBOX__DATA_WAS_SAVED'));
            } else {
                isys_notify::error($this->language->get_in_text('LC__INFOBOX__DATA_WAS_NOT_SAVED:<br />"LC__TTS__SERVICE_URL" LC__UNIVERSAL__FIELD_VALUE_IS_INVALID'));
            }
        }

        $l_edit_right = isys_module_system::getAuth()->is_allowed_to(isys_auth::EDIT, 'TTS/CONFIG');

        $l_navbar = isys_component_template_navbar::getInstance();
        $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__EDIT)
            ->set_active(false, C__NAVBAR_BUTTON__NEW)
            ->set_active(false, C__NAVBAR_BUTTON__PURGE)
            ->set_visible(true, C__NAVBAR_BUTTON__EDIT);

        if ($l_posts[C__GET__NAVMODE] == C__NAVMODE__EDIT) {
            $l_navbar->set_active($l_edit_right, C__NAVBAR_BUTTON__SAVE)
                ->set_active(true, C__NAVBAR_BUTTON__CANCEL)
                ->set_active(false, C__NAVBAR_BUTTON__EDIT)
                ->set_visible(false, C__NAVBAR_BUTTON__EDIT);
        }

        $l_settings = $l_dao_tts->get_data()->get_row();

        $l_rules = [
            'C__MODULE__REQUEST_TRACKER_CONFIG__DB_ACTIVE' => [
                'p_arData'        => get_smarty_arr_YES_NO(),
                'p_strSelectedID' => $l_settings['isys_tts_config__active'],
                'p_strClass'      => 'input-small'
            ],
            'C__MODULE__REQUEST_TRACKER_CONFIG__LINK'      => [
                'p_strValue' => $l_settings['isys_tts_config__service_url'],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__REQUEST_TRACKER_CONFIG__TLS_CERTIFICATE'      => [
                'value' => $l_settings['isys_tts_config__tls_certificate'],
            ],
            'C__MODULE__REQUEST_TRACKER_CONFIG__USER'      => [
                'p_strValue' => $l_settings['isys_tts_config__user'],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__REQUEST_TRACKER_CONFIG__PASS'      => [
                'p_strValue' => $l_settings['isys_tts_config__pass'],
                'p_strClass' => 'input-small'
            ],
            'C__MODULE__REQUEST_TRACKER_CONFIG__RT_QUEUES' => [
                'p_strValue'       => isys_application::instance()->container->get('settingsTenant')->get('tts.rt.queues', ''),
                'p_strClass'       => 'input-small',
                'p_strPlaceholder' => 'General'
            ],
            'C__TTS__TYPE'                                 => [
                'p_strSelectedID' => $l_settings['isys_tts_config__isys_tts_type__id'],
                'p_strClass'      => 'input-small'
            ]
        ];

        isys_application::instance()->container->get('template')
            ->assign('content_title', $this->language->get('LC__CMDB__TREE__SYSTEM__INTERFACE__TTS'))
            ->smarty_tom_add_rules('tom.content.bottom.content', $l_rules)
            ->include_template('contentbottomcontent', 'modules/ticketing/tts_config.tpl');

        return $this;
    }

    /**
     * @param isys_module_request $p_req
     *
     * @return $this
     */
    public function init(isys_module_request $p_req)
    {
        return $this;
    }

    /**
     * Method for adding links to the "sticky" category bar.
     *
     * @param  isys_component_template $p_tpl
     * @param  string                  $p_tpl_var
     * @param  integer                 $p_obj_id
     * @param  integer                 $p_obj_type_id
     */
    public static function process_menu_tree_links($p_tpl, $p_tpl_var, $p_obj_id, $p_obj_type_id)
    {
        if (!FeatureManager::isFeatureActive('tts-category')) {
            return;
        }

        $language = isys_application::instance()->container->get('language');

        if (isys_auth_cmdb::instance()
            ->has_rights_in_obj_and_category(isys_auth::VIEW, $p_obj_id, 'C__CATG__VIRTUAL_TICKETS')) {
            $l_tts = new isys_tts_dao(isys_application::instance()->container->get('database'));

            // Seems like we need this to prevent all the exceptions, when no connector is defined.
            if (count($l_tts->get_data()) > 0) {
                try {
                    if ($l_tts->get_config()) {
                        $l_link_data = [
                            'title' => $language->get('LC__CMDB__CATG__VIRTUAL_TICKETS'),
                            'icon'  => isys_application::instance()->www_path . 'images/axialis/basic/comment-empty.svg',
                            'link'  => "javascript:get_content_by_object('" . $p_obj_id . "', '" . C__CMDB__VIEW__LIST_CATEGORY . "', '" . defined_or_default('C__CATG__VIRTUAL_TICKETS') . "', '" . C__CMDB__GET__CATG . "');"
                        ];

                        $p_tpl->append($p_tpl_var, ['ticket' => $l_link_data], true);
                    }
                } catch (isys_exception_general $e) {
                    ;
                }
            }
        }
    }
}
