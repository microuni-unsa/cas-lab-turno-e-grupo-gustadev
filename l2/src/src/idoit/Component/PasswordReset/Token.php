<?php

namespace idoit\Component\PasswordReset;

/**
 * i-doit Password Reset Token.
 *
 * Please DO NOT use this class standalone, only retrieve if from 'PasswordResetService'.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Token
{
    public readonly int $tenantId;

    public readonly string $token;

    public readonly int $userId;

    public function __construct(string $token, int $userId, int $tenantId)
    {
        $this->token = $token;
        $this->userId = $userId;
        $this->tenantId = $tenantId;
    }
}
