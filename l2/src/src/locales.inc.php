<?php
/**
 * i-doit
 *
 * Localization!
 *
 * This class has nothing to do with the language manager used in the
 * template library, but it is responsible for date and time formatting
 * based on specific settings. We do not use system- or database- locales,
 * we define all locales on our own!
 *
 * @internal
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

if (!defined('LC_LANG')) {
    define('LC_LANG', 0);
}

class isys_locale
{
    const C__SESSION_CACHE_KEY = "user_setting";

    private static ?isys_locale $instance = null;

    private array $languages;

    private isys_component_dao_user $daoUser;

    private isys_component_database $database;

    /**
     * Current user settings (set after init()).
     */
    private array $m_userSettings = [
        LC_LANG     => null,
        LC_TIME     => null,
        LC_MONETARY => null,
        LC_NUMERIC  => null
    ];

    /**
     * Returns the dummy singleton instance (used before the login!)
     *
     * @static
     * @return  isys_locale
     * @deprecated Use service or 'isys_locales::instance()'. Will be removed in i-doit 40!
     */
    public static function dummy()
    {
        if (!isset(self::$instance)) {
            self::$instance = new isys_locale();
        }

        return self::$instance;
    }

    /**
     * Returns the singleton instance
     *
     * @param isys_component_database $database
     * @param int                     $userId
     * @return isys_locale
     * @throws Exception
     * @static
     * @deprecated Use service or 'isys_locales::instance()'. Will be removed in i-doit 40!
     */
    public static function get(isys_component_database $database, $userId)
    {
        if (!is_object($database) || !is_numeric($userId)) {
            throw new Exception("Internal Error! Could not configure localization (locales.inc.php)!");
        }

        if (!isset(self::$instance) || (isset(self::$instance) && $database !== self::$instance->database)) {
            self::$instance = new isys_locale($database);
            self::$instance->init($userId);
        } else {
            self::$instance->init($userId);
        }

        return self::$instance;
    }

    /**
     * @param isys_component_database $database
     * @param isys_component_session  $session
     * @return isys_locale
     * @deprecated Use service or 'isys_locales::instance()'. Will be removed in i-doit 40!
     */
    public static function factory(isys_component_database $database, isys_component_session $session)
    {
        return self::instance($database, $session);
    }

    /**
     * Get instance
     *
     * @return isys_locale
     * @throws Exception
     * @deprecated Use service or 'isys_locales::instance()'. Will be removed in i-doit 40!
     */
    public static function get_instance()
    {
        return self::instance(
            isys_application::instance()->container->get('database'),
            isys_application::instance()->container->get('session'),
        );
    }

    /**
     * @param isys_component_database $database
     * @param isys_component_session  $session
     * @return self
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public static function instance(isys_component_database $database, isys_component_session $session): self
    {
        if (!isset(self::$instance) || (isset(self::$instance) && $database !== self::$instance->database)) {
            self::$instance = new isys_locale($database);
        }

        self::$instance->init($session->get_user_id());

        return self::$instance;
    }

    /**
     * Resolves language by its constant.
     *
     * @param $constantOrId
     * @return string|null
     * @throws isys_exception_database
     */
    public function resolve_language_by_constant($constantOrId): ?string
    {
        if ($this->get_setting('browser_language')) {
            return $this->getPreferredLanguage();
        }

        if (is_string($constantOrId) && defined($constantOrId)) {
            $constantOrId = constant($constantOrId);
        }

        if (!is_numeric($constantOrId)) {
            return null;
        }

        $id = (int)$constantOrId;

        foreach ($this->languages as $language) {
            if ($id === $language['id']) {
                return $language['short'];
            }
        }

        return null;
    }

    /**
     * @param string $short
     * @return int|null
     */
    public function resolve_language_constant_by_short_tag(string $short): ?int
    {
        foreach ($this->languages as $language) {
            if ($language['short'] === $short) {
                return $language['id'];
            }
        }

        return null;
    }

    /**
     * Numerical format of data.
     *
     * @param mixed $p_data
     * @return string
     * @throws isys_exception_database
     */
    public function fmt_numeric($p_data)
    {
        if (!is_numeric($p_data)) {
            return $p_data;
        }

        $l_decSettings = $this->getConfig((int)$this->get_setting(LC_NUMERIC));

        return number_format(
            $p_data,
            $l_decSettings[LC_NUMERIC]['frac_digits'] ?? 2,
            $l_decSettings[LC_NUMERIC]['decimal_point'] ?? '.',
            $l_decSettings[LC_NUMERIC]['thousand_sep'] ?? ','
        );
    }

    /**
     * Formats a decimal number to a monetary one, dependent on the monetary setting made in locales.inc.php
     *
     * @param mixed       $value
     * @param bool|null   $symbolPrecedes
     * @param string|null $customSymbol
     * @return string
     * @throws isys_exception_database
     */
    public function fmt_monetary($value, ?bool $symbolPrecedes = null, ?string $customSymbol = null): string
    {
        if ($customSymbol === null && is_object($this->database)) {
            $symbol = $this->get_currency();
        } else {
            $symbol = $customSymbol;
        }

        $numericValue = $this->fmt_numeric($value);

        return $symbolPrecedes
            ? "{$symbol} {$numericValue}"
            : "{$numericValue} {$symbol}";
    }

    /**
     * Method to retrieve the configured currency symbol.
     *
     * @return string
     * @throws isys_exception_database
     */
    public function get_currency(): string
    {
        $userSettingsArray = $this->daoUser->get_user_settings();

        if (!isset($userSettingsArray['isys_user_locale__isys_currency__id']) || !$userSettingsArray['isys_user_locale__isys_currency__id']) {
            $currencyId = (int)isys_application::instance()->container->get('settingsTenant')->get('gui.currency', '1');
        } else {
            $currencyId = (int)$userSettingsArray['isys_user_locale__isys_currency__id'];
        }

        if ($currencyId === 0) {
            $query = 'SELECT isys_currency__id AS id
                FROM isys_currency
                ORDER BY isys_currency__id ASC
                LIMIT 1;';

            $currencyId = (int)$this->daoUser->retrieve($query)->get_row_value('id');
        }

        $symbolData = isys_cmdb_dao_dialog::instance($this->database)
            ->set_table('isys_currency')
            ->get_data($currencyId);

        $symbol = $symbolData['isys_currency__title'];

        if (is_string($symbol) && str_contains($symbol, ';')) {
            $symbolData = explode(';', $symbol);
            $symbol = end($symbolData);
        }

        return (string)$symbol;
    }

    /**
     * @param mixed   $p_data
     * @param boolean $p_shortFormat
     * @return mixed
     * @throws isys_exception_database
     */
    public function fmt_date($p_data, $p_shortFormat = true)
    {
        // First index is language, second index type of locale.
        $l_dateSettings = $this->getConfig((int)$this->get_setting(LC_TIME));

        if ($l_dateSettings === null || !isset($l_dateSettings[LC_TIME])) {
            // Fall back to english settings (hardcoded for now).
            $l_dateSettings = [
                // Short date format
                "d_fmt_s" => "%Y-%m-%d",
                // Long date format
                "d_fmt_l" => "%M %d, %Y",
                // Months.
                "mon"     => [
                    "January",
                    "February",
                    "March",
                    "April",
                    "May",
                    "June",
                    "July",
                    "August",
                    "September",
                    "October",
                    "November",
                    "December"
                ]
            ];
        } else {
            $l_dateSettings = $l_dateSettings[LC_TIME];
        }

        $l_data = trim(strval($p_data));

        if (is_numeric($l_data)) {
            $l_data = date("Y-m-d", $l_data);
        }

        if (strpos($l_data, "0000-00-00") !== false || strpos($l_data, C__FALLBACK_EMPTY_DATE) !== false) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $l_field = ($p_shortFormat) ? "d_fmt_s" : "d_fmt_l";

        // @see ID-8137
        $regex = "/((?:\d\d)?\d\d)-(\d\d)-(\d\d)(?:)/";

        return preg_replace_callback($regex, function ($matches) use ($l_field, $l_dateSettings) { // @see ID-4255
            if (count($matches) > 5) {
                [, , , , , $l_year, $l_month, $l_day] = $matches;
            } else {
                [, $l_year, $l_month, $l_day] = $matches;
            }

            return $this->_fmt($l_dateSettings[$l_field], [
                "d" => sprintf("%02d", $l_day),
                "m" => sprintf("%02d", $l_month),
                "M" => $l_dateSettings["mon"][$l_month - 1],
                "y" => sprintf("%02d", $l_year),
                "Y" => sprintf("%04d", $l_year)
            ]);
        }, $l_data);
    }

    /**
     * @param mixed   $p_data
     * @param boolean $p_shortFormat
     * @return mixed
     */
    public function fmt_time($p_data, $p_shortFormat = true)
    {
        $l_timeSettings = $this->getConfig((int)$this->get_setting(LC_LANG));

        if ($l_timeSettings === null || !isset($l_timeSettings[LC_TIME])) {
            // Fall back to english settings (hardcoded for now).
            $l_timeSettings = [
                // Short time format
                "t_fmt_s" => "%H:%i",
                // Long time format
                "t_fmt_l" => "%H:%i:%s",
            ];
        } else {
            $l_timeSettings = $l_timeSettings[LC_TIME];
        }

        $l_data = trim(strval($p_data));

        if (is_numeric($l_data)) {
            $l_data = date('H:i:s', $l_data);
        }

        if (strpos($l_data, "00:00:00") !== false) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $pattern = "/(\d\d)\:(\d\d)(?:\:(\d\d))?/";
        $format = $p_shortFormat ? 't_fmt_s' : 't_fmt_l';

        return preg_replace_callback($pattern, function ($matches) use ($format, $l_timeSettings) {
            $seconds = null;

            if (count($matches) > 3) {
                [, $hour, $minutes, $seconds] = $matches;
            } else {
                [, $hour, $minutes] = $matches;
            }
            $replaced = $this->_fmt($l_timeSettings[$format], [
                "H" => sprintf("%02d", $hour),
                "i" => sprintf("%02d", $minutes),
                "s" => sprintf("%02d", $seconds)
            ]);
            return $replaced;
        }, $l_data);
    }

    /**
     * Format date (and time if it's given).
     *
     * @param string  $value
     * @param bool    $shortDate
     * @param bool    $shortTime
     * @return string
     * @throws isys_exception_database
     */
    public function fmt_datetime($value, $shortDate = true, $shortTime = true)
    {
        $l_strDate = $this->fmt_date($value, $shortDate);

        // First strip the time if it's just "0".
        if (is_string($value) && str_contains($value, "00:00:00")) {
            // Directly return just the date without time.
            return $l_strDate;
        }

        // Get the formatted time from the string.
        return $this->fmt_time($l_strDate, $shortTime);
    }

    /**
     * Sets a user's locale setting
     *
     * @param int $setting
     * @param int $language
     * @return bool
     */
    public function set_setting($setting, $language): bool
    {
        $config = $this->getConfig((int)$language);

        if ($config !== null && array_key_exists($setting, $config)) {
            $this->m_userSettings[$setting] = $language;

            return true;
        }

        return false;
    }

    /**
     * Get a user's locale setting.
     *
     * @param mixed    $setting
     * @param int|null $default
     * @return int|null
     */
    public function get_setting(mixed $setting, int|null $default = ISYS_LANGUAGE_ENGLISH): int|null
    {
        $settingValue = $this->m_userSettings[$setting] ?? $default;

        if ($setting == LC_LANG && !is_numeric($settingValue) && is_string($settingValue)) {
            $settingValue = $this->resolve_language_constant_by_short_tag($settingValue);
        }

        if (is_numeric($settingValue)) {
            return (int)$settingValue;
        }

        return null;
    }

    /**
     * Overloading method for getting a property.
     *
     * @param string $p_setting
     * @return integer
     * @deprecated Will be removed in i-doit 40!
     */
    public function __get($p_setting)
    {
        return $this->get_setting(constant($p_setting));
    }

    /**
     * Overloading method for setting a property.
     *
     * @param string  $p_setting
     * @param integer $p_language
     * @deprecated Will be removed in i-doit 40!
     */
    public function __set($p_setting, $p_language)
    {
        $this->set_setting(constant($p_setting), $p_language);
    }

    /**
     * Initialize the per user's locale settings.
     *
     * @param integer $p_user_id
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function init($p_user_id)
    {
        // @see ID-11762 If the language was changed, record it and switch off 'browser language' feature.
        if ($_SESSION['recordLanguageChanged']) {
            // Remove previously cached data.
            $_SESSION[self::C__SESSION_CACHE_KEY] = null;

            $this->daoUser->save_settings([
                'C__CATG__OVERVIEW__LANGUAGE' => $this->retrieveLanguageIdByAbbreviation(isys_application::instance()->language),
                'C__CATG__OVERVIEW__BROWSER_LANGUAGE' => false
            ]);

            $_SESSION['recordLanguageChanged'] = false;
        }

        // Are the locales already cached?
        if (!empty($_SESSION[self::C__SESSION_CACHE_KEY]) && is_array($_SESSION[self::C__SESSION_CACHE_KEY]) && count($_SESSION[self::C__SESSION_CACHE_KEY])) {
            $this->m_userSettings = $_SESSION[self::C__SESSION_CACHE_KEY];

            if (!is_numeric($this->m_userSettings[LC_LANG]) && is_string($this->m_userSettings[LC_LANG])) {
                $this->m_userSettings[LC_LANG] = $this->resolve_language_constant_by_short_tag($this->m_userSettings[LC_LANG]);
            }
        } else {
            if ($p_user_id !== null) {
                $l_res = $this->get_settings_by_user_id((int)$p_user_id);

                if ($l_res !== null) {
                    $l_langrow = $l_res->get_row();

                    if (!empty($l_langrow["isys_user_locale__language"])) {
                        $this->m_userSettings[LC_LANG] = $l_langrow["isys_user_locale__language"];
                    } else {
                        // @see  ID-8780  Set the default language by setting
                        $this->m_userSettings[LC_LANG] = $this->resolve_language_constant_by_short_tag((string)isys_tenantsettings::get('system.default-language', 'en'));

                        // @see ID-11762 React to 'browser detection' setting.
                        if ($this->m_userSettings[LC_LANG] === null) {
                            $this->m_userSettings[LC_LANG] = $this->getPreferredLanguage();
                        }
                    }

                    if ($l_langrow["isys_user_locale__language_time"] > 0) {
                        $this->m_userSettings[LC_TIME] = $l_langrow["isys_user_locale__language_time"];
                    }

                    try {
                        $userSettingsArray = $this->daoUser->get_user_settings();
                        $this->m_userSettings[LC_MONETARY] = $userSettingsArray['isys_user_locale__isys_currency__id'];
                    } catch (isys_exception_database $e) {
                        $this->m_userSettings[LC_MONETARY] = defined_or_default('C__CMDB__CURRENCY__EURO', 1);
                    }

                    if ($l_langrow["isys_user_locale__language_numeric"] > 0) {
                        $this->m_userSettings[LC_NUMERIC] = $l_langrow["isys_user_locale__language_numeric"];
                    }

                    $this->m_userSettings['tree_type'] = isys_application::instance()->container->get('settingsUser')->get('gui.default-tree-type', C__CMDB__VIEW__TREE_LOCATION__LOCATION);

                    if (isset($l_langrow["isys_user_locale__browser_language"])) {
                        $this->m_userSettings['browser_language'] = $l_langrow["isys_user_locale__browser_language"];
                    }
                }
            }

            $_SESSION[self::C__SESSION_CACHE_KEY] = $this->m_userSettings;
        }
    }

    /**
     * @param int $setting
     * @return array
     */
    public function get_user_settings($setting)
    {
        // @See ID-4682 Default language should always be english
        $langId = ISYS_LANGUAGE_ENGLISH;

        // @see ID-11111 Only fetch 'browser language' if we fetch the user language, not numeric values etc.
        if ($setting == LC_LANG && $this->get_setting('browser_language')) {
            $languages = [];

            foreach ($this->languages as $language) {
                if (!$language['available']) {
                    continue;
                }

                $languages[$language['short']] = $language['id'];
            }

            $preferredLanguage = $this->getPreferredLanguage();

            if (isset($languages[$preferredLanguage])) {
                $langId = $languages[$preferredLanguage];

                return $this->getConfig((int)$langId)[$setting] ?? null;
            }
        }

        if (isset($this->m_userSettings[$setting])) {
            $langId = $this->m_userSettings[$setting];
        }

        return $this->getConfig((int)$langId)[$setting] ?? null;
    }

    /**
     * Reset (and Rebuild) the user locale cache
     *
     * @param bool|integer $p_rebuild
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    public function reset_cache($p_rebuild = false)
    {
        /* Reset the Cache */
        $_SESSION[self::C__SESSION_CACHE_KEY] = null;

        /* Rebuild the cache */
        if ($p_rebuild) {
            $l_user_id = (is_numeric($p_rebuild)) ? $p_rebuild : isys_application::instance()->container->get('session')->get_user_id();

            $this->init($l_user_id);
        }
    }

    /**
     * Gets the date format
     *
     * @param bool $shortFormat
     * @param bool $sanitizeFormat
     * @return string
     * @deprecated Will be removed in i-doit 40!
     */
    public function get_date_format($shortFormat = true, $sanitizeFormat = true)
    {
        $dateFormat = ($this->date_time_format_helper(($shortFormat ? 'd_fmt_m' : 'd_fmt_l')) ?: ($shortFormat ? 'Y-m-d' : 'd. M, Y'));

        return ($sanitizeFormat) ? str_replace('%', '', $dateFormat) : $dateFormat;
    }

    /**
     * Gets the time format
     *
     * @param bool $shortFormat
     * @param bool $sanitizeFormat
     * @return string
     * @deprecated Will be removed in i-doit 40!
     */
    public function get_time_format($shortFormat = true, $sanitizeFormat = true)
    {
        $dateFormat = ($this->date_time_format_helper(($shortFormat ? 't_fmt_s' : 't_fmt_l')) ?: ($shortFormat ? 'H:i' : 'H:i:s'));

        return ($sanitizeFormat) ? str_replace('%', '', $dateFormat) : $dateFormat;
    }

    /**
     * Helper method to retrieve the specified date, time format
     *
     * @param $p_key
     * @return mixed
     */
    private function date_time_format_helper($p_key)
    {
        $l_date_settings = $this->get_user_settings(LC_TIME);

        return $l_date_settings[$p_key];
    }

    /**
     * @return mixed
     * @throws Exception
     * @deprecated Use translations instead. Will be removed in i-doit 40!
     */
    public function get_month_names()
    {
        $sessionLanguage = (string)isys_application::instance()->container->get('session')->get_language();
        $id = $this->resolve_language_constant_by_short_tag($sessionLanguage);

        if (!$id) {
            $id = ISYS_LANGUAGE_ENGLISH;
        }

        $configuration = $this->getConfig((int)$id);

        if ($configuration === null || !isset($configuration[LC_TIME]['mon'])) {
            // Fall back to english settings (hardcoded for now).
            return [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December"
            ];
        }

        return $configuration[LC_TIME]['mon'];
    }

    /**
     * _fmt method.
     *
     * @param string $p_string
     * @param array  $p_repArray
     * @return mixed
     */
    private function _fmt($p_string, $p_repArray)
    {
        foreach ($p_repArray as $l_key => $l_value) {
            $p_string = str_replace("%" . $l_key, $l_value, $p_string);
        }

        return $p_string;
    }

    /**
     * @param string $abbreviation
     * @return int|null
     * @throws isys_exception_database
     */
    private function retrieveLanguageIdByAbbreviation(string $abbreviation): ?int
    {
        $systemDao = isys_cmdb_dao::instance(isys_application::instance()->container->get('database_system'));

        // Fetch language ID by given abbreviation.
        $query = 'SELECT isys_language__id AS id
            FROM isys_language
            WHERE isys_language__short = ' . $systemDao->convert_sql_text($abbreviation) . '
            LIMIT 1;';

        $languageId = $systemDao->retrieve($query)->get_row_value('id');

        return is_numeric($languageId) ? (int)$languageId : null;
    }

    /**
     * Returns the user settings by the user-ID
     *
     * @param int $p_user_id
     * @return isys_component_dao_result|null
     * @throws isys_exception_dao
     * @throws isys_exception_database
     */
    private function get_settings_by_user_id(int $p_user_id): ?isys_component_dao_result
    {
        if ($p_user_id > 0) {
            $l_q = "SELECT * FROM isys_user_locale
				INNER JOIN isys_user_setting ON isys_user_locale__isys_user_setting__id = isys_user_setting__id
				WHERE isys_user_setting__isys_obj__id = " . $p_user_id . ";";

            $l_res = $this->daoUser->retrieve($l_q);

            if ($l_res->num_rows() > 0) {
                return $l_res;
            } else {
                $languageAbbreviation = isys_application::instance()->container->get('session')->get_language();

                $defaultLanguage = isys_application::instance()->container->get('settingsTenant')->get('system.default-language', null);

                // @see ID-11762 Set the browser language as current language, if none is set.
                if ($languageAbbreviation === null) {
                    if ($defaultLanguage === null || $defaultLanguage == C__USE_BROWSER_DEFAULT) {
                        $languageAbbreviation = $this->getPreferredLanguage();
                    } else {
                        $languageAbbreviation = $defaultLanguage;
                    }
                }

                // Fetching the language at this point will most likely return the abbreviation ('de', 'en', ...).
                if (!is_numeric($languageAbbreviation) && is_string($languageAbbreviation) && !empty($languageAbbreviation)) {
                    $languageAbbreviation = $this->retrieveLanguageIdByAbbreviation($languageAbbreviation);
                }

                if ($this->daoUser->get_user_setting_id($p_user_id)) {
                    $userSettingsId = $this->daoUser->convert_sql_id($this->daoUser->get_user_setting_id());
                    // Don't use 'convert_sql_id' because the field is not nullable.
                    $languageId = $this->daoUser->convert_sql_int($languageAbbreviation);

                    // Otherwise create record and try again.
                    $replaceQuery = "REPLACE INTO isys_user_locale (
                            isys_user_locale__isys_user_setting__id,
                            isys_user_locale__language,
                            isys_user_locale__language_time,
                            isys_user_locale__language_numeric
                        ) VALUES (
                            {$userSettingsId},
                            {$languageId},
                            {$languageId},
                            {$languageId}
						);";

                    $this->daoUser->begin_update();

                    if ($this->daoUser->update($replaceQuery) && $this->daoUser->apply_update()) {
                        return $this->get_settings_by_user_id($p_user_id);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPreferredLanguage(): string
    {
        // Open version has only english language file. See ID-4409
        if (!isys_application::isPro()) {
            return 'en';
        }

        $supportedLanguages = array_map(fn ($language) => $language['short'], $this->languages);

        $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        preg_match_all('/([a-z]{2,3}(?:-[A-Z]{2})?)(?:;q=([0-9.]+))?/', $header, $matches, PREG_SET_ORDER);

        $languages = [];
        foreach ($matches as $match) {
            // For example 'de-de' or 'en'.
            $lang = strtolower($match[1]);

            // Standard 'Q' value is 1.0.
            $q = isset($match[2]) ? (float) $match[2] : 1.0;

            $languages[$lang] = $q;
        }

        // Sort by priority (by 'Q' value).
        arsort($languages);

        // Return first supported language.
        foreach (array_keys($languages) as $lang) {
            if (in_array($lang, $supportedLanguages, true)) {
                return $lang;
            }

            // For language regions ('de-DE', 'en-US', ...).
            $baseLang = explode('-', $lang)[0];
            if (in_array($baseLang, $supportedLanguages, true)) {
                return $baseLang;
            }
        }

        // Return english as fallback.
        return 'en';
    }

    /**
     * Constructor
     *
     * @param isys_component_database|null $database
     * @throws isys_exception_database
     * @deprecated Will be removed (= made private) in i-doit 40!
     */
    public function __construct(?isys_component_database $database = null)
    {
        if ($database) {
            $this->database = $database;
            $this->daoUser = isys_component_dao_user::instance($database);
        }

        // @see ID-11111 Fetch all languages to use them later.
        $this->languages = [];

        $query = "SELECT
            isys_language__id AS id,
            isys_language__title AS title,
            isys_language__short AS short,
            isys_language__icon AS icon,
            isys_language__available AS available,
            isys_language__const AS constant,
            isys_language__sort AS sort
            FROM isys_language;";

        $result = (new isys_component_dao(isys_application::instance()->container->get('database_system')))->retrieve($query);

        while ($row = $result->get_row()) {
            $row['id'] = (int)$row['id'];
            $row['available'] = (bool)$row['available'];
            $row['sort'] = (int)$row['sort'];

            $this->languages[] = $row;
        }

        // Fallback for i-doit OPEN.
        if (!defined('ISYS_LANGUAGE_GERMAN')) {
            define('ISYS_LANGUAGE_GERMAN', 2);
        }
    }

    /**
     * Refactor member variable to this method to prevent undefined 'ISYS_LANGUAGE_ENGLISH' constant (bootstrapping issue).
     *
     * @param int $languageId
     * @return array|null
     */
    private function getConfig(int $languageId): ?array
    {
        $configuration = [
            // Extend this in order to make a new language or localisation configuration.
            ISYS_LANGUAGE_ENGLISH => [
                // Language settings:
                LC_LANG     => [],
                // Time settings.
                LC_TIME     => [
                    // Short date format
                    "d_fmt_s" => "%Y-%m-%d",
                    // Short date format but long year
                    "d_fmt_m" => "%Y-%m-%d",
                    // Long date format
                    "d_fmt_l" => "%M %d, %Y",
                    // Short time format
                    "t_fmt_s" => "%H:%i",
                    // Long time format
                    "t_fmt_l" => "%H:%i:%s",
                    // Months.
                    "mon"     => [
                        "January",
                        "February",
                        "March",
                        "April",
                        "May",
                        "June",
                        "July",
                        "August",
                        "September",
                        "October",
                        "November",
                        "December"
                    ]
                ],
                // Monetary and currency settings.
                LC_MONETARY => [
                    // EUR x,xxx,xxx,xxx
                    "mon_thousands_sep" => ",",
                    // EUR x,xxx.yy
                    "mon_decimal_point" => ".",
                    // Grouping for the value (EUR 1,000,000)
                    "mon_grouping"      => 3,
                    // EUR 1999,xx
                    "int_frac_digits"   => 2,
                    // EUR 1999,xx
                    "frac_digits"       => 2,
                    // 1 if int_curr_symbol stands in front of the value, 0 if behind
                    "p_ics_precedes"    => 0,
                ],
                // Numeric settings.
                LC_NUMERIC  => [
                    "decimal_point" => ".",
                    "thousand_sep"  => ",",
                    "grouping"      => 3,
                    "frac_digits"   => 2
                ]
            ],
            ISYS_LANGUAGE_GERMAN  => [
                // Language settings:
                LC_LANG     => [],
                // Time settings.
                LC_TIME     => [
                    // Short date format
                    "d_fmt_s" => "%d.%m.%y",
                    // Short date format but long year
                    "d_fmt_m" => "%d.%m.%Y",
                    // Long date format
                    "d_fmt_l" => "%d. %M %Y",
                    // Short time format
                    "t_fmt_s" => "%H:%i",
                    // Long time format
                    "t_fmt_l" => "%H:%i:%s",
                    // Months.
                    "mon"     => [
                        "Januar",
                        "Februar",
                        "März",
                        "April",
                        "Mai",
                        "Juni",
                        "Juli",
                        "August",
                        "September",
                        "Oktober",
                        "November",
                        "Dezember"
                    ]
                ],
                // Monetary and currency settings.
                LC_MONETARY => [
                    // EUR x.xxx.xxx.xxx
                    "mon_thousands_sep" => ".",
                    // EUR xxxx,yy
                    "mon_decimal_point" => ",",
                    // Grouping for the value (EUR 1.000.000)
                    "mon_grouping"      => 3,
                    // EUR 1999,xx
                    "int_frac_digits"   => 2,
                    // EUR 1999,xx
                    "frac_digits"       => 2,
                    // 1 if int_curr_symbol stands in front of the value, 0 if behind
                    "p_ics_precedes"    => 0,
                ],
                // Numeric settings.
                LC_NUMERIC  => [
                    "decimal_point" => ",",
                    "thousand_sep"  => ".",
                    "grouping"      => 3,
                    "frac_digits"   => 2
                ]
            ]
        ];

        return $configuration[$languageId] ?? null;
    }
}
