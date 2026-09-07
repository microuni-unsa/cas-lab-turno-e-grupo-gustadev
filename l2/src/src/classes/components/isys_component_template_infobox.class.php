<?php

/**
 * i-doit
 *
 * gets the current content of the logbook for the infobox.
 *
 * @internal
 * @package     i-doit
 * @subpackage  Components_Template
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template_infobox
{
    private static $m_instance = null;

    protected $m_nAlertLevel;

    protected $m_nMessageID;

    protected $m_strMessage;

    /**
     * @return self
     */
    public static function instance(): self
    {
        if (self::$m_instance === null) {
            self::$m_instance = new self;
        }

        return self::$m_instance;
    }

    /**
     * @param $p_message
     * @param $p_messageID
     * @param $p_m_nAlertLevel
     *
     * @return $this
     */
    public function set_message($p_message, $p_messageID = null, $p_m_nAlertLevel = null)
    {
        $this->m_strMessage = $p_message;

        // Our own messages get id 0 and are internal.
        if (is_numeric($p_messageID)) {
            $this->m_nMessageID = $p_messageID;
        } else {
            $this->m_nMessageID = 0;
        }

        if (is_numeric($p_m_nAlertLevel)) {
            $this->m_nAlertLevel = $p_m_nAlertLevel;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function show_html()
    {
        if (!isys_module_logbook::getAuth()->is_allowed_to(isys_auth::VIEW, 'INFOBOX')) {
            return '';
        }

        $database = isys_application::instance()->container->get('database');
        $language = isys_application::instance()->container->get('language');
        $locales = isys_application::instance()->container->get('locales');

        $url = '';
        $title = '';
        $message = '';
        $alertLevelColor = '';
        $date = null;

        if ($database) {
            // Use DAO to get last entry in logbook.
            try {
                $lastLogEntry = isys_component_dao_logbook::instance($database)->get_result_latest_entry()->get_row();

                // If set_message() was used don't do anything.
                if (is_null($this->m_strMessage)) {
                    if (!empty($lastLogEntry)) {
                        $l_m_strMessage = isys_event_manager::getInstance()->translateEvent(
                            $lastLogEntry['isys_logbook__event_static'],
                            $lastLogEntry['isys_logbook__obj_name_static'],
                            $lastLogEntry['isys_logbook__category_static'],
                            $lastLogEntry['isys_logbook__obj_type_static'],
                            $lastLogEntry['isys_logbook__entry_identifier_static'],
                            $lastLogEntry['isys_logbook__changecount']
                        );

                        $this->m_nAlertLevel = defined_or_default($lastLogEntry['isys_logbook_level__const'], 0);
                        $this->m_strMessage = $l_m_strMessage;
                        $this->m_nMessageID = $lastLogEntry['isys_logbook__id'];
                        $date = $locales->fmt_datetime($lastLogEntry['isys_logbook__date']);
                    } else {
                        $this->m_nAlertLevel = defined_or_default('C__LOGBOOK__ALERT_LEVEL__0', 0);
                        $this->m_strMessage = $language->get('LC__INFOBOX__NO_ENTRIES');
                        $this->m_nMessageID = 0;
                    }
                }
            } catch (isys_exception $e) {
                echo $e->getMessage();
            }
        }

        if ($this->m_nAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__1', 1)) {
            $alertLevelColor = '-green';
        } elseif ($this->m_nAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__2', 2)) {
            $alertLevelColor = '-yellow';
        } elseif ($this->m_nAlertLevel == defined_or_default('C__LOGBOOK__ALERT_LEVEL__3', 3)) {
            $alertLevelColor = '-red';
        }

        if ($this->m_nMessageID != 0) {
            $url = isys_helper_link::create_url([
                C__GET__MODULE_ID => defined_or_default('C__MODULE__LOGBOOK'),
                C__GET__ID        => $this->m_nMessageID
            ]);

            $title = $language->get('LC__INFOBOX__TITLE');
        }

        if (!empty($this->m_strMessage)) {
            $message = $this->m_strMessage;

            if ($date !== null) {
                $message = $date . ' ' . $this->m_strMessage . ' [' . $lastLogEntry['isys_logbook__user_name_static'] . ']';
            }
        }

        $icon = '<img title="' . $title . '" alt="" src="' . isys_application::instance()->www_path . 'images/axialis/basic/button-info' . $alertLevelColor . '.svg" />';

        if (!empty($url)) {
            $icon = '<a title="' . $title . '" href="' . $url . '">' . $icon . '</a>';
        }

        return $icon . '<span>' . html_entity_decode(stripslashes($message), ENT_COMPAT, BASE_ENCODING) . '</span>';
    }
}
