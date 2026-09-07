<?php

use idoit\AddOn\AuthableInterface;
use idoit\AddOn\ExtensionProviderInterface;
use idoit\Component\Settings\SettingsCollection;
use idoit\Component\Settings\Types\IntSetting;
use idoit\Component\Settings\Types\SelectSetting;
use idoit\Event\System\Settings\ExtendTenantSettings;
use idoit\Module\Search\Query\Condition;
use idoit\Module\Search\SearchExtension;

/**
 * i-doit
 *
 * Search module
 *
 * @package     i-doit
 * @subpackage  Modules
 * @version     1.7
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_module_search extends isys_module implements AuthableInterface, ExtensionProviderInterface
{
    // Define, if this module shall be displayed in the named menus.
    const DISPLAY_IN_MAIN_MENU   = false;
    const DISPLAY_IN_SYSTEM_MENU = false;

    // Define, that this module uses a "pretty" URL.
    const MAIN_MENU_REWRITE_LINK = true;

    const AUTOMATIC_DEEP_SEARCH_ACTIVE              = 2;
    const AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT = 1;
    const AUTOMATIC_DEEP_SEARCH_NONACTIVE           = 0;

    /**
     * @var bool
     */
    protected static $m_licenced = true;

    /**
     * @param isys_module_request $p_req
     *
     * @return boolean
     */
    public function init(isys_module_request $p_req)
    {
        return is_object($p_req);
    }

    /**
     * Retrieves a bookmark string for mydoit.
     *
     * @param   string $p_text
     * @param   string $p_link
     *
     * @author  Kevin Mauel <kmauel@i-doit.org>
     *
     * @return  bool    true
     */
    public function mydoit_get(&$p_text, &$p_link)
    {
        $p_text[] = str_replace('{0}', $_GET['q'], isys_application::instance()->container->get('language')
            ->get('LC__MODULE__SEARCH__FOR'));
        $p_link = 'moduleID=' . defined_or_default('C__MODULE__SEARCH') . '&q=' . urlencode($_GET['q']);

        return true;
    }

    /**
     * Returns the module's container extension.
     *
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new SearchExtension();
    }

    /**
     * Method for retrieving the path to the module directory (needed for includes).
     *
     * @static
     * @return  string
     */
    public static function get_dir()
    {
        return __DIR__;
    }

    /**
     * @return isys_auth_search
     */
    public static function getAuth()
    {
        return isys_auth_search::instance();
    }

    /**
     * @param ExtendTenantSettings $event
     *
     * @return void
     * @throws isys_exception_database
     */
    public static function extendTenantSettings(ExtendTenantSettings $event): void
    {
        $language = isys_application::instance()->container->get('language');

        $event->addSettingsCollection(
            new SettingsCollection('LC__UNIVERSAL__SEARCH', [
                new SelectSetting(
                    'defaults.search.mode',
                    'LC__SEARCH__CONFIG__MODE',
                    $language->get_in_text('LC__SEARCH__CONFIG__SUGGESTION_NOTE<br /><br />
                        LC__SEARCH__CONFIG__NORMAL_DESCRIPTION<br /><br />
                        LC__SEARCH__CONFIG__DEEP_DESCRIPTION'),
                    '0',
                    Condition::$modes
                ),
                new SelectSetting(
                    'search.use-fuzzy-search',
                    'LC__SEARCH__CONFIG__FUZZY_SEARCH',
                    'LC__SEARCH__CONFIG__FUZZY_SEARCH_DESCRIPTION',
                    '0',
                    [
                        '0' => 'LC__UNIVERSAL__NO',
                        '1' => 'LC__UNIVERSAL__YES'
                    ]
                ),
                new SelectSetting(
                    'search.global.autostart-deep-search',
                    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH',
                    'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_DESCRIPTION',
                    isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE,
                    [
                        isys_module_search::AUTOMATIC_DEEP_SEARCH_ACTIVE              => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_ACTIVE',
                        isys_module_search::AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_ACTIVE_EMPTY_RESULT',
                        isys_module_search::AUTOMATIC_DEEP_SEARCH_NONACTIVE           => 'LC__SEARCH__CONFIG__AUTOMATIC_DEEP_SEARCH_NONACTIVE'
                    ]
                ),
                new SelectSetting(
                    'search.highlight-search-string',
                    'LC__SEARCH__CONFIG__HIGHLIGHTING_OPTION',
                    'LC__SEARCH__CONFIG__HIGHLIGHTING_OPTION_DESCRIPTION',
                    '1',
                    [
                        '0' => 'LC__UNIVERSAL__NO',
                        '1' => 'LC__UNIVERSAL__YES'
                    ]
                ),
                new IntSetting(
                    'search.minlength.search-string',
                    'LC__SEARCH__CONFIG__MINLENGTH_SEARCHSTRING',
                    'LC__SEARCH__CONFIG__MINLENGTH_SEARCHSTRING_DESCRIPTION',
                    3,
                    3
                ),
                new SelectSetting(
                    'search.index.include_archived_deleted_objects',
                    'LC__SEARCH__CONFIG__INDEX__INCLUDE_ARCHIVED_DELETED_OBJECTS',
                    'LC__SEARCH__CONFIG__INDEX__INCLUDE_ARCHIVED_DELETED_OBJECTS__DESCRIPTION',
                    '0',
                    [
                        '0' => 'LC__UNIVERSAL__NO',
                        '1' => 'LC__UNIVERSAL__YES'
                    ]
                ),
                new SelectSetting(
                    'search.index.location_paths',
                    'LC__SEARCH__CONFIG__INDEX__LOCATION_PATHS',
                    'LC__SEARCH__CONFIG__INDEX__LOCATION_PATHS__DESCRIPTION',
                    '0',
                    [
                        '0' => 'LC__UNIVERSAL__NO',
                        '1' => 'LC__UNIVERSAL__YES'
                    ]
                ),
            ])
        );
    }
}
