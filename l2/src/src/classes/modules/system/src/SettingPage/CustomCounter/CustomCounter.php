<?php

namespace idoit\Module\System\SettingPage\CustomCounter;

use idoit\Module\System\SettingPage\SettingPage;
use isys_auth;
use isys_auth_system_globals;
use isys_component_template_navbar;
use isys_notify;
use isys_tenantsettings;

class CustomCounter extends SettingPage
{
    const NAME_PATTERN = 'cmdb.counter.';

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    private $counters = [];

    /**
     * @var bool
     */
    private $error = false;

    /**
     * @var string
     */
    private $error_message;

    /**
     * @param int $navMode
     *
     * @return mixed|void
     */
    public function renderPage($navMode)
    {
        $auth = isys_auth_system_globals::instance();

        $auth->check(isys_auth::VIEW, 'GLOBALSETTINGS/CUSTOMCOUNTER');

        if (isset($_GET[C__GET__ID]) && !empty($_GET[C__GET__ID]) && $navMode != C__NAVMODE__EDIT && $navMode != C__NAVMODE__SAVE) {
            $navMode = C__NAVMODE__EDIT;
        }

        isys_component_template_navbar::getInstance()->set_active(
            $auth->is_allowed_to(isys_auth::CREATE, 'GLOBALSETTINGS/CUSTOMCOUNTER'),
            C__NAVBAR_BUTTON__SAVE
        );

        if ($navMode == C__NAVMODE__SAVE) {
            $auth->check(isys_auth::CREATE, 'GLOBALSETTINGS/CUSTOMCOUNTER');

            $this->counters = $_POST['counter'] ?? [];
            $this->prepareNewCounters($_POST['new_counters']);

            if (!$this->error) {
                $this->saveCounters();
                unset($_POST['new_counters']);

                isys_notify::success($this->lang->get('LC__UNIVERSAL__SUCCESSFULLY_SAVED'));
            }
        }

        $this->renderList();
    }

    /**
     *
     */
    private function saveCounters()
    {
        foreach ($this->counters as $counter) {
            $title = self::NAME_PATTERN . strtolower($counter['key']);
            $value = $counter['value'];
            $is_deleted = isset($counter['is_deleted']) ? $counter['is_deleted'] : 0;
            if ($is_deleted) {
                isys_tenantsettings::remove($title);
                continue;
            }
            isys_tenantsettings::set($title, $value);
        }

        isys_tenantsettings::force_save();
    }

    /**
     * @param $counters
     *
     * @return false|void
     */
    private function prepareNewCounters($counters)
    {
        $lang = \isys_application::instance()->container->get('language');

        if (!isset($counters['key']) || !is_array($counters['key'])) {
            return false;
        }

        for ($i = 0;$i < count($counters['key']);$i++) {
            if (strlen($counters['key'][$i]) == 0) {
                $this->error = true;
                $this->error_message = $lang->get('LC__COUNTER__NAME_REQUIRED');

                return false;
            }

            if (preg_match("/[^A-Za-z0-9_#]/", $counters['key'][$i])) {
                $this->error = true;
                $this->error_message = $lang->get('LC__COUNTER__NAME_CONTAINS');

                return false;
            }

            if (!is_numeric($counters['value'][$i])) {
                $this->error = true;
                $this->error_message = $lang->get('LC__COUNTER__NUMERIC_TYPE');

                return false;
            }

            if (key_exists('COUNTER_' . strtoupper($counters['key'][$i]), $this->counters)) {
                $this->error = true;
                $this->error_message = $lang->get('LC__COUNTER__EXISTS', $counters['key'][$i]);

                return false;
            }

            $this->counters[$counters['key'][$i]] = [
                'key'   => 'COUNTER_' . $counters['key'][$i],
                'value' => $counters['value'][$i]
            ];
        }
    }

    /**
     *
     */
    private function renderList()
    {
        $this->settings = isys_tenantsettings::getLike(self::NAME_PATTERN);
        $this->prepareTitles();
        $rules = [
            'count_of_custom_counters' => [
                'p_strClass' => 'input-mini'
            ]
        ];

        if ($this->error) {
            isys_notify::warning($this->error_message, ['sticky' => true]);
        }

        $new_counters = [];
        if (isset($_POST['new_counters'])) {
            $new_counters = $_POST['new_counters']['key'];
        }
        $this->tpl
            ->assign('content_title', $this->lang->get('LC__UNIVERSAL__CUSTOM_COUNTER'))
            ->assign('data', $this->settings)
            ->assign('new_counters', $new_counters)
            ->assign('canCreate', isys_auth_system_globals::instance()
                ->is_allowed_to(isys_auth::CREATE, 'GLOBALSETTINGS/CUSTOMCOUNTER'))
            ->assign('canEdit', isys_auth_system_globals::instance()
                ->is_allowed_to(isys_auth::EDIT, 'GLOBALSETTINGS/CUSTOMCOUNTER'))
            ->assign('isSupervisor', isys_auth_system_globals::instance()
                ->is_allowed_to(isys_auth::SUPERVISOR, 'GLOBALSETTINGS/CUSTOMCOUNTER'))
            ->activate_editmode()
            ->smarty_tom_add_rule('tom.content.navbar.cRecStatus.p_bInvisible=1')
            ->smarty_tom_add_rules('tom.content.bottom.content', $rules)
            ->include_template('contentbottomcontent', 'modules/system/custom_counter.tpl');
    }

    /**
     *
     */
    private function prepareTitles()
    {
        $settings = [];
        foreach ($this->settings as $key => $value) {
            if (!empty($key)) {
                $new_title = strtoupper(str_replace(self::NAME_PATTERN, '', $key));
                $settings[$new_title] = $value === null ? 0 : $value;
            }
        }
        $this->settings = $settings;
    }
}
