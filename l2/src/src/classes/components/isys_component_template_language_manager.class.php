<?php

/**
 * i-doit
 *
 * Language manager used by the template library. It is responsible for managing the language caches.
 *
 * @package     i-doit
 * @subpackage  Components_Template
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @version     Dennis Stücken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_template_language_manager extends isys_component
{
    /**
     * @var array
     */
    private array $translationCache;

    /**
     * @var array
     */
    private array $customTranslationCache;

    /**
     * Variable which holds the loaded language.
     *
     * @var  string
     */
    private $m_language = 'en';

    /**
     *
     * @global  isys_component_database $g_comp_database_system
     * @return  array
     */
    public function fetch_available_languages()
    {
        global $g_comp_database_system;

        $l_return = [];
        $l_q = $g_comp_database_system->query("SELECT * FROM isys_language WHERE isys_language__available = 1;");

        while ($l_lang = $g_comp_database_system->fetch_row_assoc($l_q)) {
            $l_return[] = $l_lang;
        }

        return $l_return;
    }

    /**
     * @param string $languageConstant
     *
     * @return string
     */
    public function get_in_text($languageConstant): string
    {
        $languageConstant = (string)$languageConstant;

        if (str_contains($languageConstant, 'LC_')) {
            // Replaces all language constant inside a string.
            return preg_replace_callback("/(LC_[A-Za-z\_0-9]+)/", function ($matches) use ($languageConstant) {
                if (count($matches)) {
                    $languageKey = current($matches);

                    // @see ID-9220 We prioritize the custom translations.
                    if (isset($this->customTranslationCache[$languageKey])) {
                        return $this->customTranslationCache[$languageKey];
                    }

                    if (isset($this->translationCache[$languageKey])) {
                        return $this->translationCache[$languageKey];
                    }

                    // @see  ID-5550  When the constant is set use it - even if it's "0".
                    return $languageKey;
                }

                return $languageConstant;
            }, $languageConstant);
        }

        return $this->get($languageConstant);
    }

    /**
     * Returns the language string specified by the language identifier ($languageConstant) and an optional array for substituting parameters ($variables).
     *
     * @param string $languageConstant
     * @param mixed  $variables
     *
     * @return string
     */
    public function get($languageConstant, ...$variables): string
    {
        $languageConstant = (string)$languageConstant;
        $variables = array_filter($variables, fn ($var) => $var !== null);

        if (!empty($languageConstant) && (isset($this->customTranslationCache[$languageConstant]) || isset($this->translationCache[$languageConstant]))) {
            if (isset($variables[0])) {
                $translation = vsprintf(
                    $this->customTranslationCache[$languageConstant] ?? $this->translationCache[$languageConstant],
                    is_array($variables[0]) ? $variables[0] : $variables
                );
            } else {
                $translation = $this->customTranslationCache[$languageConstant] ?? $this->translationCache[$languageConstant];
            }
        } else {
            // If the Language constant is not defined, directly output it, so you know, what is missing.
            return $languageConstant;
        }

        /*
         * Match again for language constants in evaluated language strings and replace them. We are not using the iterative variant here (e.g. enumerating
         * through the array with language constants) - instead we're matching directly for existing language constants and replace them (using substr_replace, which
         * is faster than preg_replace in our case). If a Language constant has more than one language constants in it, we have to recalculate the substition offsets.
         */
        if (isset($variables[0]) && strpos($translation, '[{') !== false) {
            if (preg_match_all("/\[\{(.*?)\}\]/i", $translation, $l_regex, PREG_OFFSET_CAPTURE)) {
                $l_d_offset = 0;

                $count = is_countable($l_regex[0]) && count($l_regex[0]);
                for ($l_i = 0;$l_i < $count;$l_i++) {
                    /*
                     * If using PREG_OFFSET_CAPTURE with preg_match_all, we get a 3-dimensional array:
                     *  1. Dimension: 0 = Original data, 1 = First regex-group, 2 = Second regex-group and so on
                     *  2. Dimension: Index of search result
                     *  3. Dimension: 0 = Data, 1 = Offset
                     */
                    $l_source = $l_regex[0][$l_i][0];
                    $l_const = $l_regex[1][$l_i][0];
                    $l_offset = $l_regex[0][$l_i][1];

                    // This is necessary since we don't want a recursive loop.
                    if ($l_const != $languageConstant) {
                        // Fetch data for language constant.
                        $l_newdata = $this->get($l_const);

                        if (is_array($variables[0])) {
                            if (array_key_exists($l_const, $variables[0])) {
                                $l_newdata = $this->get($variables[0]["$l_const"]);
                            }
                        }

                        // Recalculate substition offsets.
                        $l_offset -= $l_d_offset;
                        $l_d_offset += (strlen($l_source) - strlen($l_newdata));

                        // Do substitution.
                        $translation = substr_replace($translation, $l_newdata, $l_offset, strlen($l_source));
                    }
                }
            }
        }

        return (string)$translation;
    }

    /**
     * Retrieves the currently loaded language as string (for example "de" or "en").
     *
     * @return  string
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function get_loaded_language()
    {
        return $this->m_language;
    }

    /**
     * @param $languageConstant
     *
     * @return string
     * @deprecated Will be removed in i-doit 40!
     */
    public function __get($languageConstant)
    {
        return $this->get($languageConstant);
    }

    /**
     * @return array
     * @deprecated Please use 'getCache()'. Will be removed in i-doit 40!
     */
    public function get_cache(): array
    {
        return $this->getCache();
    }

    /**
     * @return array
     */
    public function getCache(): array
    {
        return $this->translationCache;
    }

    /**
     * @return array
     */
    public function getCustomCache(): array
    {
        return $this->customTranslationCache;
    }

    /**
     * Loads and creates, if necessary, the language cache into self::$translationCache.
     *
     * @param string|null $language
     *
     * @return void
     */
    public function load(?string $language = null): void
    {
        global $g_absdir;

        if ($language !== null) {
            $this->m_language = str_replace(chr(0), '', $language);
        }

        $this->translationCache = [];
        $this->customTranslationCache = [];

        $mainFolder = "{$g_absdir}/src/lang";

        $this->load_folder($mainFolder);

        foreach (glob("{$g_absdir}/src/classes/modules/*/lang") as $folder) {
            $this->load_folder($folder);
        }

        $this->load_custom($this->m_language, $mainFolder);
    }

    /**
     * Load the language from the folder.
     *
     * @param string $folder
     *
     * @return void
     * @deprecated Will be removed (= made private) in i-doit 40!
     */
    public function load_folder(string $folder): void
    {
        $file = "{$folder}/{$this->m_language}.inc.php";

        if (!file_exists($file) || strstr($this->m_language, "/")) {
            $file = "{$folder}/en.inc.php";
        }

        $this->append_lang_file($file);
    }

    /**
     * Loads the custom language file, if available.
     *
     * @param $p_language_short
     * @param $folder
     *
     * @return void
     * @deprecated Will be removed (= made private) in i-doit 40!
     */
    public function load_custom($p_language_short = null, $folder = null): void
    {
        global $g_absdir;

        if ($p_language_short !== null) {
            $this->m_language = str_replace(chr(0), '', $p_language_short);
        }
        if (is_null($folder)) {
            $folder = "{$g_absdir}/src/lang";
        }

        $this->append_lang_file("{$folder}/{$this->m_language}_custom.inc.php", true);
    }

    /**
     * Method for generically adding new translations.
     *
     * @param array $p_language_array
     * @param bool  $custom
     *
     * @return $this
     * @deprecated Will be removed (= made private) in i-doit 40!
     * @todo Change return type to 'void' once it is private.
     */
    public function append_lang(array $p_language_array = [], bool $custom = false)
    {
        if ($custom) {
            $this->customTranslationCache = array_merge($this->customTranslationCache, $p_language_array);
        } else {
            $this->translationCache = array_merge($this->translationCache, $p_language_array);
        }

        return $this;
    }

    /**
     * Method for generically adding new translations.
     *
     * @param string $languageFilePath
     * @param bool   $custom
     *
     * @return $this
     * @deprecated Will be removed (= made private) in i-doit 40!
     * @todo Change return type to 'void' once it is private.
     */
    public function append_lang_file(string $languageFilePath, bool $custom = false)
    {
        global $g_langcache;

        if (file_exists($languageFilePath)) {
            $g_langcache = [];
            // Aufgrund von RT#27300 verwenden wir kein include_once.
            $l_lang = include $languageFilePath;

            if (is_array($l_lang)) {
                return $this->append_lang($l_lang, $custom);
            }

            if (isset($g_langcache) && is_countable($g_langcache) && count($g_langcache) > 0) {
                return $this->append_lang($g_langcache, $custom);
            }

            unset($l_lang);
        }

        return $this;
    }

    /**
     * Calls load with $p_language_id as language identifier.
     *
     * @param  string $p_language_short
     */
    public function __construct($p_language_short)
    {
        $this->translationCache = [];
        $this->customTranslationCache = [];

        $this->load($p_language_short);
    }
}
