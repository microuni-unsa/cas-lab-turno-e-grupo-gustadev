<?php

/**
 * i-doit
 *
 * Dashboard widget class
 *
 * @package     i-doit
 * @subpackage  Modules
 * @version     1.2
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_dashboard_widgets_notes extends isys_dashboard_widgets
{
    /**
     * @return bool
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param array $p_row
     * @param int   $p_id
     *
     * @return string
     * @throws SmartyException
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $l_rules = [
            'title'     => $this->m_config['title'],
            'color'     => $this->m_config['color'],
            'fontcolor' => $this->m_config['fontcolor'],
            'note'      => $this->m_config['note']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', $this->language->get('LC__WIDGET__NOTES__CONFIG'))
            ->assign('rules', $l_rules)
            ->assign('unique_id', $p_id)
            ->fetch(__DIR__ . '/templates/config.tpl');
    }

    /**
     * Render method.
     *
     * @param $p_unique_id
     *
     * @return string
     * @throws SmartyException
     */
    public function render($p_unique_id)
    {
        $l_stripped_content = trim(strip_tags($this->m_config['note']));
        $l_empty = empty($l_stripped_content);
        $l_link = isys_helper_link::create_url([
            C__GET__AJAX      => 1,
            C__GET__AJAX_CALL => 'dashboard',
            'func'            => 'update_widget'
        ]);

        return $this->m_tpl->assign('ajax_url', $l_link)
            ->assign('unique_id', $p_unique_id)
            ->assign('title', $this->m_config['title'])
            ->assign('color', $this->m_config['color'])
            ->assign('fontcolor', $this->m_config['fontcolor'])
            ->assign('note', $l_empty ? $this->language->get('LC__WIDGET__NOTES__DEFAULT') : $this->m_config['note'])
            ->assign('note_empty', $l_empty)
            ->fetch(__DIR__ . '/templates/notes.tpl');
    }
}
