<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_welcome extends isys_dashboard_widgets
{
    /**
     * Path and Filename of the configuration template.
     *
     * @var string
     */
    protected $m_config_tpl_file = '';

    /**
     * Path and Filename of the template.
     *
     * @var string
     */
    protected $m_tpl_file = '';

    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return bool
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * @param array $p_config
     *
     * @return isys_dashboard_widgets_welcome
     * @throws Exception
     */
    public function init($p_config = [])
    {
        $this->m_tpl_file = __DIR__ . '/templates/welcome.tpl';
        $this->m_config_tpl_file = __DIR__ . '/templates/config.tpl';

        return parent::init($p_config);
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param array $p_row
     * @param int   $p_id
     *
     * @return false|string
     * @throws SmartyException
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_rules = [
            'animate'    => $this->m_config['animate'],
            'salutation' => $this->m_config['salutation'],
        ];

        $l_name_data = $this->get_name_data(isys_application::instance()->container->get('session')->get_user_id());

        $l_salutation_options = [
            'a' => $this->language->get('LC__WIDGET__WELCOME__GREETING_A'),
            'b' => $this->language->get('LC__WIDGET__WELCOME__GREETING_B'),
            'c' => $this->language->get('LC__WIDGET__WELCOME__GREETING_C'),
            'd' => $this->language->get('LC__WIDGET__WELCOME__GREETING_D')
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', $this->language->get('LC__WIDGET__WELCOME__CONFIG'))
            ->assign('salutation_options', array_merge_recursive($l_salutation_options, $l_name_data['options']))
            ->assign('rules', $l_rules)
            ->fetch($this->m_config_tpl_file);
    }

    /**
     * Render method.
     *
     * @param string $p_unique_id
     *
     * @return false|string
     * @throws SmartyException
     */
    public function render($p_unique_id)
    {
        $l_name_data = $this->get_name_data(isys_application::instance()->container->get('session')->get_user_id());

        $l_date = $this->language->get('LC__WIDGET__WELCOME__DATE', [date('H:i'), isys_application::instance()->container->get('locales')->fmt_date(time(), false)]);

        return $this->m_tpl->assign('animate', $this->m_config['animate'])
            ->assign('unique_id', $p_unique_id)
            ->assign('salutation', $l_name_data['options'][$this->m_config['salutation']])
            ->assign('date', $l_date)
            ->fetch($this->m_tpl_file);
    }

    /**
     * @param $p_user_id
     *
     * @return array
     */
    protected function get_name_data($p_user_id)
    {
        $l_dao = isys_cmdb_dao_category_s_person_master::instance($this->database);

        $l_salutation = '';
        $l_person_row = $l_dao->get_data(null, $p_user_id)
            ->get_row();

        if (!empty($l_person_row['isys_cats_person_list__salutation'])) {
            $l_salutation = $l_dao->callback_property_salutation();
            $l_salutation = $l_salutation[$l_person_row['isys_cats_person_list__salutation']];
        }

        $l_name_data = [
            'greeting'   => isys_helper_textformat::get_daytime(),
            'salutation' => $l_salutation,
            'title'      => $l_person_row['isys_cats_person_list__academic_degree'],
            'first_name' => $l_person_row['isys_cats_person_list__first_name'],
            'last_name'  => $l_person_row['isys_cats_person_list__last_name'],
            'options'    => []
        ];

        $l_name_data['options'] = [
            'a' => isys_helper_textformat::get_daytime() . ' ' . $l_name_data['first_name'] . ' ' . $l_name_data['last_name'],
            'b' => isys_helper_textformat::get_daytime() . ' ' . $l_name_data['salutation'] . ' ' . $l_name_data['last_name'],
            'c' => isys_helper_textformat::get_daytime() . ' ' . $l_name_data['salutation'] . ' ' . $l_name_data['title'] . ' ' . $l_name_data['last_name'],
            'd' => $this->language->get('LC_UNIVERSAL__HELLO') . ' ' . $l_name_data['first_name']
        ];

        return $l_name_data;
    }
}
