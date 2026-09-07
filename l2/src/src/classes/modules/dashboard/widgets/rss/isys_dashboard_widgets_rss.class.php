<?php

use SimplePie\Misc;
use SimplePie\SimplePie;

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
class isys_dashboard_widgets_rss extends isys_dashboard_widgets
{
    /**
     * Returns a boolean value, if the current widget has an own configuration page.
     *
     * @return  boolean
     */
    public function has_configuration()
    {
        return true;
    }

    /**
     * Method for loading the widget configuration.
     *
     * @param   array   $p_row The current widget row from "isys_widgets".
     * @param   integer $p_id  The ID from "isys_widgets_config".
     *
     * @return  string
     */
    public function load_configuration(array $p_row, $p_id)
    {
        $rules = [
            'url'   => $this->m_config['url'],
            'count' => $this->m_config['count']
        ];

        return $this->m_tpl->activate_editmode()
            ->assign('title', $this->language->get('LC__WIDGET__RSS__CONFIG'))
            ->assign('rules', $rules)
            ->fetch(__DIR__ . '/templates/config.tpl');
    }

    /**
     * Render method.
     *
     * @param   string $p_unique_id
     *
     * @return  string
     */
    public function render($p_unique_id)
    {
        $rssLibrary = new SimplePie();
        $rssLibrary->set_feed_url($this->m_config['url']);
        $rssLibrary->set_item_limit($this->m_config['count']);
        $rssLibrary->set_cache_location(BASE_DIR . 'temp');
        $rssLibrary->set_useragent(SimplePie::NAME . '/' . SimplePie::VERSION . ' via i-doit (Feed Parser; ' . SimplePie::URL . '; Allow like Gecko) Build/' . Misc::get_build());
        $rssLibrary->set_output_encoding(BASE_ENCODING);

        // @see ID-11624 Prepare proxy options, moved here from removed 'isys_library_simplepie'.
        if (isys_settings::get('proxy.active', false)) {
            $curlOptions = [
                CURLOPT_PROXY     => isys_settings::get('proxy.host'),
                CURLOPT_PROXYPORT => isys_settings::get('proxy.port'),
            ];

            if (isys_settings::get('proxy.username')) {
                $curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_ANYSAFE;
                $curlOptions[CURLOPT_PROXYUSERPWD] = sprintf(
                    '%s:%s',
                    isys_settings::get('proxy.username'),
                    isys_settings::get('proxy.password'),
                );
            }

            $rssLibrary->set_curl_options($curlOptions);
        }

        $rssLibrary->init();

        $items = [];
        $locale = isys_application::instance()->container->get('locales');

        foreach ($rssLibrary->get_items(0, $this->m_config['count']) as $item) {
            $items[] = [
                'url'         => $item->get_permalink(),
                'title'       => $item->get_title(),
                'description' => $item->get_description(),
                'date'        => $locale->fmt_datetime($item->get_date('Y-m-d H:i:s')),
            ];
        }

        return $this->m_tpl
            ->assign('items', $items)
            ->assign('rss', $rssLibrary)
            ->fetch(__DIR__ . '/templates/rss.tpl');
    }
}
