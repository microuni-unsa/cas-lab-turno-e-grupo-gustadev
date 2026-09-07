<?php

namespace idoit\Component\Security\Hash;

/**
 * i-doit Password hashing compoonente
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PasswordVerify
{

    /**
     * @param $password
     * @param $hash
     *
     * @return bool
     */
    public function verify($password, $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * @return PasswordVerify
     */
    public static function instance(): PasswordVerify
    {
        return new self();
    }
}
