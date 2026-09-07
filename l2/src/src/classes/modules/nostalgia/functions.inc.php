<?php
/**
 * i-doit
 *
 * Old i-doit core functions. Keeping these for compability reasons.
 *
 * @package     modules
 * @subpackage  nostalgia
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       i-doit 1.9
 */

// LF: Removed "_get_browser" in i-doit 29.
// LF: Removed "isys_glob_bin2ip" in i-doit 29.
// LF: Removed "isys_glob_ip2bin" in i-doit 29.
// LF: Removed "isys_glob_assert_callback" in i-doit 1.14.
// LF: Removed "isys_glob_utf8_encode" in i-doit 36.
// LF: Removed "isys_glob_utf8_decode" in i-doit 29.

if (!function_exists('_L')) {
    /**
     * Get language constant from template language manager.
     *
     * @deprecated
     *
     * @param   string $p_language_constant
     * @param   mixed  $p_values
     *
     * @return  string
     */
    function _L($p_language_constant, $p_values = null)
    {
        // @see ID-9220 Only strings are translatable!
        if (!is_string($p_language_constant)) {
            return '';
        }

        return isys_application::instance()->container->get('language')
            ->get($p_language_constant, $p_values);
    }
}

if (!function_exists('_LL')) {
    /**
     * Get language constant from template language manager - usable for translations in strings.
     *
     * @deprecated
     *
     * @param   string $p_language_constant
     * @param   mixed  $p_values
     *
     * @return  string
     */
    function _LL($p_language_constant, $p_values = null)
    {
        // @see ID-9220 Only strings are translatable!
        if (!is_string($p_language_constant)) {
            return '';
        }

        return isys_application::instance()->container->get('language')
            ->get_in_text($p_language_constant);
    }
}

// LF: Removed "isys_glob_days_in_month" in i-doit 29.
// LF: Removed "is_valid_hostname" in i-doit 36.
// LF: Removed "isys_glob_is_valid_hostname" in i-doit 36.
// LF: Removed "isys_glob_create_tcp_address" in i-doit 36.
// LF: Removed "isys_glob_prepare_string" in i-doit 36.
// LF: Removed "isys_glob_format_datetime" in i-doit 36.

if (!function_exists('isys_strlen')) {
    /**
     * Function which will return the string length. Will use mb_strlen if available.
     *
     * @param string $p_string
     *
     * @return  integer
     * @deprecated
     */
    function isys_strlen($p_string)
    {
        return (function_exists('mb_strlen') ? mb_strlen($p_string, BASE_ENCODING) : strlen($p_string));
    }
}

// LF: Removed "gettime" in i-doit 36.
// LF: Removed "isys_glob_die" in i-doit 36.
// LF: Removed "isys_glob_var_export" in i-doit 36.

if (!function_exists('isys_array_merge_keys')) {
    /**
     * Array_merge that preserves keys, truly accepts an arbitrary number of arguments, and saves space on the stack (non recursive).
     *
     * @return array
     *
     * @deprecated
     */
    function isys_array_merge_keys()
    {
        $l_result = [];
        $l_args = func_get_args();

        foreach ($l_args as $l_array) {
            foreach ($l_array as $l_key => $l_value) {
                $l_result[$l_key] = $l_value;
            }
        }

        return $l_result;
    }
}

if (!function_exists('isys_glob_str_replace')) {
    /**
     * Replace entries in $p_arr in $p_str. [KEY] is substituted by value in array.
     *
     * @param string $p_str
     * @param array  $p_arr
     *
     * @return string
     *
     * @deprecated
     */
    function isys_glob_str_replace($p_str, $p_arr)
    {
        if (is_array($p_arr)) {
            foreach ($p_arr as $l_subst => $l_val) {
                $p_str = str_replace("[" . $l_subst . "]", $l_val, $p_str);
            }

            return $p_str;
        }

        return null;
    }
}

if (!function_exists('isys_glob_reset_type')) {
    /**
     * Resets a variable type. Also detects booleans.
     *
     * @param mixed &$p_var
     *
     * @deprecated
     */
    function isys_glob_reset_type(&$p_var)
    {
        $l_vartype = gettype($p_var);

        if ($l_vartype === 'string') {
            if ($p_var === 'true' || $p_var === 'false') {
                $l_vartype = 'boolean';
            }
        }

        settype($p_var, $l_vartype);
    }
}

if (!function_exists('print_ar')) {
    /**
     * function for dumping a formatted output on screen (helpful for debugging).
     *
     * @param mixed $p_value
     *
     * @deprecated
     */
    function print_ar($p_value)
    {
        if (empty($p_value)) {
            echo 'Content is empty!';
        } else {
            echo '<pre>' . var_export($p_value, true) . '</pre>';
        }
    }
}

if (!function_exists('isys_glob_mkdate')) {
    /**
     * Makes a formatted date from p_datestring using strtotime.
     *
     * @param string $p_datestring
     * @param string $p_format
     *
     * @return string
     * @deprecated
     */
    function isys_glob_mkdate($p_datestring, $p_format)
    {
        return date($p_format, strtotime($p_datestring));
    }
}

if (!function_exists('isys_glob_datetime')) {
    /**
     * Returns the current date and time in datetime syntax: "YYYY-MM-DD HH:MM".
     *
     * @return string
     * @deprecated
     */
    function isys_glob_datetime()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('isys_stristr')) {
    /**
     * stristr compatibility function
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool|false|string
     * @deprecated
     */
    function isys_stristr($haystack, $needle)
    {
        return (function_exists('mb_stristr') ? mb_stristr($haystack, $needle) : stristr($haystack, $needle));
    }
}

// LF: Removed custom "array_find" in i-doit 38.

if (!function_exists('isys_glob_htmlspecialchars')) {
    /**
     * html_specialchars Wrapper.
     *
     * @param string  $p_val
     * @param integer $p_flags
     * @param string  $p_encoding
     * @param boolean $p_double_enc
     *
     * @return string
     * @deprecated
     */
    function isys_glob_htmlspecialchars($p_val, $p_flags = ENT_QUOTES, $p_encoding = null, $p_double_enc = false)
    {
        return htmlspecialchars($p_val, $p_flags, $p_encoding ?: BASE_ENCODING, $p_double_enc);
    }
}

if (!function_exists('isys_glob_url_remove')) {
    /**
     * Removes a GET parameter from an URL.
     *
     * @param string &$p_url
     * @param string  $p_parameter
     *
     * @return string
     * @deprecated Use isys_helper_link::remove_params_from_url();
     */
    function isys_glob_url_remove($p_url, $p_parameter)
    {
        $p_url = preg_replace("/(\?)" . $p_parameter . "=(.+?)(&|$)/", "\\1", $p_url);
        $p_url = preg_replace("/(&)" . $p_parameter . "=(.+?)(&|$)/", "\\3", $p_url);

        return $p_url;
    }
}

if (!function_exists('isys_glob_is_valid_ip')) {
    /**
     * True, if param is a valid ip v4 address.
     *
     * @param string $p_ip
     *
     * @return boolean
     * @deprecated Use methods from idoit\Component\Helper\Ip
     */
    function isys_glob_is_valid_ip($p_ip)
    {
        return (preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $p_ip) == 0) ? false : true;
    }
}

if (!function_exists('isys_glob_is_valid_ip6')) {
    /**
     * Validates an ip v6 address.
     *
     * @param string $p_ip
     *
     * @return boolean
     * @deprecated Use methods from idoit\Component\Helper\Ip
     */
    function isys_glob_is_valid_ip6($p_ip)
    {
        if (preg_match('/^[A-F0-9]{0,5}:[A-F0-9:]{1,39}$/i', $p_ip)) {
            $l_p = explode(':::', $p_ip);
            if (count($l_p) > 1) {
                return false;
            }

            $l_p = explode('::', $p_ip);
            if (count($l_p) > 2) {
                return false;
            }

            $l_p = explode(':', $p_ip);

            if (count($l_p) > 8) {
                return false;
            }

            foreach ($l_p as $l_checkPart) {
                if (strlen($l_checkPart) > 4) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}

if (!function_exists('isys_glob_js_string')) {
    /**
     * Returns a string javascript-formatted
     *
     * @param string $p_string
     *
     * @return string
     * @deprecated
     */
    function isys_glob_js_string($p_string): string
    {
        if (is_scalar($p_string)) {
            return "''";
        }

        return "'" . str_replace(["\\\\", "\n", "'"], ["\\", "\\n", "\\'"], (string)$p_string) . "'";
    }
}

if (!function_exists('verbose')) {
    /**
     * Display message in verbose mode.
     *
     * @param string $message
     * @param bool   $newline
     * @param string $star
     *
     * @deprecated
     */
    function verbose($message, $newline = true, $star = "")
    {
        if (defined("ISYS_VERBOSE")) {
            echo $newline ? PHP_EOL : '';

            if ($star) {
                echo "[" . $star . "] ";
            }

            echo $message;
        }
    }
}

if (!function_exists('isys_glob_date_diff')) {
    /**
     * Gets the difference between two dates, dates need to be timestamps.
     * This is some old code (Don't know who wrote it) greatly updated by Leo (Snatched some code from the Kohana Framework).
     *
     * @param integer $p_date_from
     * @param integer $p_date_to
     * @param string  $p_format
     *
     * @return array
     * @deprecated
     */
    function isys_glob_date_diff($p_date_from = null, $p_date_to = null, $p_format = 'ymwdhis')
    {
        $p_date_from = $p_date_from ?: time();
        $p_date_to = $p_date_to ?: time();
        $l_diff = abs($p_date_to - $p_date_from);
        $p_format = array_unique(str_split(strtolower($p_format)));
        $l_return = [];

        if (in_array('y', $p_format)) {
            $l_diff -= isys_convert::YEAR * ($l_return['y'] = (int)floor($l_diff / isys_convert::YEAR));
        }

        if (in_array('m', $p_format)) {
            $l_diff -= isys_convert::MONTH * ($l_return['m'] = (int)floor($l_diff / isys_convert::MONTH));
        }

        if (in_array('w', $p_format)) {
            $l_diff -= isys_convert::WEEK * ($l_return['w'] = (int)floor($l_diff / isys_convert::WEEK));
        }

        if (in_array('d', $p_format)) {
            $l_diff -= isys_convert::DAY * ($l_return['d'] = (int)floor($l_diff / isys_convert::DAY));
        }

        if (in_array('h', $p_format)) {
            $l_diff -= isys_convert::HOUR * ($l_return['h'] = (int)floor($l_diff / isys_convert::HOUR));
        }

        if (in_array('i', $p_format)) {
            $l_diff -= isys_convert::MINUTE * ($l_return['i'] = (int)floor($l_diff / isys_convert::MINUTE));
        }

        if (in_array('s', $p_format)) {
            $l_return['s'] = $l_diff;
        }

        return $l_return;
    }
}

if (!function_exists('isys_glob_template_handler')) {
    /**
     * Default Template Handler. Called when Smarty's file: resource is unable to load a requested file.
     *
     * @param string                   $p_res_type Resource type (e.g. "file", "string", "eval", "resource")
     * @param string                   $p_res_name Resource name (e.g. "foo/bar.tpl")
     * @param string                  &$p_template_source
     * @param integer                 &$p_template_timestamp
     * @param isys_component_template  $p_smarty_obj
     *
     * @return  mixed  Path to file or boolean true if $content and $modified have been filled, boolean false if no default template could be loaded
     * @deprecated
     */
    function isys_glob_template_handler($p_res_type, $p_res_name, &$p_template_source, &$p_template_timestamp, isys_component_template $p_smarty_obj)
    {
        if ($p_res_type === 'file' && !is_readable($p_res_name)) {
            $l_default_file = __DIR__ . '/themes/default/smarty/templates/' . $p_res_name;
            $l_default_file = str_replace('./', '', $l_default_file);

            if ($l_default_file && file_exists($l_default_file)) {
                $p_template_timestamp = time();
                $p_template_source = file_get_contents($l_default_file);

                return true;
            }
        }

        return false;
    }
}

if (!function_exists('isys_glob_get_param_invert')) {
    /**
     * Give post params higher priority than get
     *
     * @param string $p_key
     *
     * @return  mixed  post or get param
     * @deprecated
     */
    function isys_glob_get_param_invert($p_key)
    {
        // @see  ID-3485  Filter parameters before returning.
        if (isset($_POST[$p_key])) {
            return filter_var($_POST[$p_key], FILTER_SANITIZE_SPECIAL_CHARS);
        }

        if (isset($_GET[$p_key])) {
            return filter_var($_GET[$p_key], FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return false;
    }
}

if (!function_exists('isys_glob_get_mandator_cache_dir')) {
    /**
     * Get the directory name for mandator-cache.
     *
     * @param integer $p_id The (optional) mandator-ID.
     *
     * @return  string
     * @deprecated
     */
    function isys_glob_get_mandator_cache_dir($p_id = null)
    {
        if (null === $p_id) {
            $p_id = $_SESSION['user_mandator'];
        }

        return 'cache_' . isys_glob_get_mandant_name_as_string($p_id);
    }
}
