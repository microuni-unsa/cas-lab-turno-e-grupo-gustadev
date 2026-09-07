<?php

namespace idoit\Component\PasswordReset;

/**
 * i-doit Password Reset User Info.
 *
 * Please DO NOT use this class standalone, only retrieve if from 'PasswordResetService'.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class UserInfo
{
    public readonly int $tenantId;

    public readonly string $tenantName;

    public readonly int $userId;

    public readonly string $userName;

    public function __construct(int $userId, string $userName, int $tenantId, string $tenantName)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->tenantId = $tenantId;
        $this->tenantName = $tenantName;
    }
}
