<?php

use phpseclib\Crypt\Random;
use phpseclib\Crypt\Rijndael;

/**
 * i-doit
 *
 * Helper methods for Crypting via phpseclib
 *
 * @package     i-doit
 * @subpackage  Helper
 * @author      Kevin Mauel <kmauel@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.0
 */
class isys_helper_crypt
{
    /**
     * Default delimiter
     *
     * @type string
     */
    const DELIMITER = '|$$|';

    /**
     * Encrypt a string.
     *
     * @param  string $string
     *
     * @return string
     * @author Kevin Mauel <kmauel@i-doit.org>
     */
    public static function encrypt($string)
    {
        global $g_crypto_hash;

        $cipher = new Rijndael();
        $cipher->setKey($g_crypto_hash);
        $l_iv = Random::string($cipher->getBlockLength() >> 3);
        $cipher->setIV($l_iv);

        $l_crypted_string = $l_iv . self::DELIMITER . $cipher->encrypt($string);

        return base64_encode($l_crypted_string);
    }

    /**
     * Decrypt a string.
     *
     * @param  string $string
     *
     * @return string|boolean
     * @author Kevin Mauel <kmauel@i-doit.org>
     */
    public static function decrypt($string)
    {
        global $g_crypto_hash;

        // Decrypted password
        $decryptedPassword = false;

        // Check whether string is really a string
        if (is_string($string)) {
            // Prepare encrypting password
            $base64decoding = base64_decode($string, true);

            // Check whether delimiter is included
            if (strpos($base64decoding, self::DELIMITER) !== false) {
                // Destruct crypted string into its parts
                $cryptedArray = explode(self::DELIMITER, $base64decoding);

                // Prepare and run decrypting routine
                $cipher = new Rijndael();
                $cipher->setKey($g_crypto_hash);
                $cipher->setIV($cryptedArray[0]);

                try {
                    // Decrypt password
                    $password = $cipher->decrypt($cryptedArray[1]);
                } catch (\phpseclib3\Exception\BadDecryptionException $e) {
                    $language = isys_application::instance()->container->get('language');

                    isys_application::instance()->container->get('notify')->error(
                        $language->get('LC__CMDB__CATG__PASSWORD__DECRYPTION_ERROR') . '<br /><br />' . $e->getMessage(),
                        ['sticky' => true]
                    );
                } catch (Exception $e) {
                    isys_application::instance()->container->get('notify')->error(
                        $e->getMessage(),
                        ['sticky' => true]
                    );
                }

                // Validate decrypted password
                if (self::validateDecryption($password)) {
                    $decryptedPassword = $password;
                }
            }
        }

        return $decryptedPassword;
    }

    /**
     * Validate decrypted password
     *
     * @param string $string
     *
     * @return bool
     * @author Selcuk Kekec <skekec@i-doit.com>
     */
    public static function validateDecryption($string)
    {
        // Needed because of some possible inconsistencies in libiconv
        ini_set('mbstring.substitute_character', 'none');

        // Convert UTF-8 to UTF-8//Ignore - Ignore unknown characters
        $stringWithoutUnknownChars = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        // Check whether string length of target and source are the same
        if (strlen($stringWithoutUnknownChars) == strlen($string)) {
            return true;
        }

        return false;
    }

    /**
     * Password Encryption method
     *
     * @param $string
     *
     * @return string
     *
     * @author Denis Koroliov <dkorolov@i-doit.com>
     */
    public static function encryptPassword($string)
    {
        global $g_security;
        global $g_crypto_hash;
        $string = $g_crypto_hash.$string;

        switch ($g_security['passwords_encryption_method']) {
            case 'argon2i':
                $password_hash = password_hash($string, PASSWORD_ARGON2I);
                break;
            case 'bcrypt':
            default:
                $password_hash = password_hash($string, PASSWORD_BCRYPT);
                break;
        }

        return $password_hash;
    }

    /**
     * Check is password correct
     *
     * @param     $login_password
     * @param     $user_password
     * @param int $should_migrated
     *
     * @return bool
     *
     * @author Denis Koroliov <dkorolov@i-doit.com>
     */
    public static function checkPassword($login_password, $user_password, $should_migrated = 0)
    {

        global $g_crypto_hash;

        if ($should_migrated) {
            $md5_pass = md5($login_password);
            return password_verify($g_crypto_hash.$md5_pass, $user_password);
        }

        $login_password = $g_crypto_hash.$login_password;
        return password_verify($login_password, $user_password);
    }

    /**
     * Check is password needs to be rehashed
     *
     * @param $user_pass
     *
     * @return bool
     *
     * @author Denis Koroliov <dkorolov@i-doit.com>
     */
    public static function isPasswordNeedsRehash($user_pass)
    {
        global $g_security;

        $config_encryption_method = $g_security['passwords_encryption_method'];
        $password_data = password_get_info($user_pass);
        $current_encryption_method = $password_data['algoName'];

        return $config_encryption_method !== $current_encryption_method;
    }

    /**
     * @return string
     */
    public static function getEncryptionMethod(): string
    {
        global $g_security;

        if (isset($g_security['passwords_encryption_method'])) {
            if ($g_security['passwords_encryption_method'] === 'bcrypt') {
                return 'bcrypt';
            }

            if ($g_security['passwords_encryption_method'] === 'argon2i') {
                return 'argon2i';
            }
        }

        return defined('PASSWORD_ARGON2I') ? 'argon2i' : 'bcrypt';
    }
}
