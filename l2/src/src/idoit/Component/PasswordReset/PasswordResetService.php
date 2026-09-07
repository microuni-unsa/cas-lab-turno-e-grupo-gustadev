<?php

namespace idoit\Component\PasswordReset;

use idoit\Component\Logger;
use idoit\Component\Settings\Environment;
use isys_component_template;
use isys_library_mail;

/**
 * i-doit Password Reset Service.
 *
 * Use this class to process the 'password reset' functionality.
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PasswordResetService
{
    private const TOKEN_TTL = '15 minutes';
    public const TOKEN_INITIAL_TTL = '48 hours';
    private const TOKEN_RANDOM_BYTES = 24;
    public const SUBJECT_INITIAL_PASSWORD =  'Set your new password for your i-doit instance';

    private static PasswordResetService $instance;

    private Logger $logger;

    private \isys_cmdb_dao $systemDao;

    public static function instance(\isys_component_database $systemDatabase): PasswordResetService
    {
        if (!isset(self::$instance)) {
            self::$instance = new static($systemDatabase);
        }

        return self::$instance;
    }

    /**
     * Check if the feature is active for any or a specific tenant.
     *
     * @param int|null $tenantId
     *
     * @return bool
     * @throws \isys_exception_database
     */
    public function isActive(?int $tenantId = null): bool
    {
        if ($tenantId === null) {
            // Check if the feature is active in any tenant
            $query = "SELECT COUNT(1) AS cnt
                FROM isys_settings
                WHERE isys_settings__key = 'system.passwort-reset.enabled'
                AND isys_settings__value = 1
                AND isys_settings__isys_mandator__id != 0;";
        } else {
            // Check if the feature is active in any tenant
            $query = "SELECT COUNT(1) AS cnt
                FROM isys_settings
                WHERE isys_settings__key = 'system.passwort-reset.enabled'
                AND isys_settings__value = 1
                AND isys_settings__isys_mandator__id = {$tenantId};";
        }

        return (bool)$this->systemDao->retrieve($query)
            ->get_row_value('cnt');
    }

    /**
     * @param string $token
     *
     * @return bool
     * @throws \isys_exception_database
     */
    public function tokenExists(string $token): bool
    {
        return $this->findByToken($token) !== null;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function hasPasswordCorrectLength(string $password): bool
    {
        return strlen($password) >= 8 && strlen($password) <= 64;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function hasPasswordCorrectDisposition(string $password): bool
    {
        return preg_match('/[A-Z]+/', $password) === 1 &&
            preg_match('/[a-z]+/', $password) === 1 &&
            preg_match('/[0-9]+/', $password) === 1;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function isPasswordValid(string $password): bool
    {
        // @todo: Do we have to check for special chars here also or is it just a advice?
        return $this->hasPasswordCorrectLength($password) && $this->hasPasswordCorrectDisposition($password);
    }

    /**
     * Trigger 'forgot password' process, will send out e-mails to users.
     *
     * @param string $email
     * @param string $ttl
     *
     * @return void
     * @throws \DateMalformedStringException
     * @throws \isys_exception_dao
     */
    public function requestForgottenPassword(string $email, string $ttl = self::TOKEN_TTL): void
    {
        $userInfos = $this->findUsersByEmail($email);

        $resetEmailData = [];
        foreach ($userInfos as $userInfo) {
            $token = $this->createToken($userInfo->userId, $userInfo->tenantId, $ttl);

            $resetEmailData[] = [
                'userInfo'  => $userInfo,
                'resetLink' => $this->getResetLink($token),
            ];
        }

        if ($resetEmailData) {
            $this->sendResetEmails($email, $resetEmailData, $ttl);
        } else {
            $this->logger->warning("No users found with e-mail address {$email}.");
        }
    }

    /**
     * @param UserInfo $userInfo
     * @param string   $email
     * @param string   $ttl
     * @param string   $subject
     *
     * @return bool
     * @throws \DateMalformedStringException
     * @throws \SmartyException
     * @throws \isys_exception_dao
     */
    public function requestInitialPasswordSet(UserInfo $userInfo, string $email, string $ttl = self::TOKEN_INITIAL_TTL, string $subject = self::SUBJECT_INITIAL_PASSWORD): bool
    {
        $token = $this->createToken($userInfo->userId, $userInfo->tenantId, $ttl);
        $data = [
            'userInfo'  => $userInfo,
            'resetLink' => $this->getResetLink($token)
        ];

        /** @var isys_component_template $template */
        $template = \isys_application::instance()->container->get('template');

        $content = $template->assign('email', $email)
            ->assign('resetEmailData', $data)
            ->assign('ttl', $ttl)
            ->assign('instanceUrl', Environment::getBaseUri())
            ->fetch('email/password_set.tpl');

        try {
            $mailer = new \isys_library_mail(true);
            $mailer->set_charset('UTF-8')
                ->set_backend(isys_library_mail::C__BACKEND__SMTP)
                // @todo Define subject
                ->set_subject($subject)
                ->set_body($content)
                ->set_content_type(isys_library_mail::C__CONTENT_TYPE__HTML);

            $mailer->addAddress($email);

            return $mailer->send();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reset passwort for given token.
     *
     * @param string $token
     * @param string $newPassword
     *
     * @return bool
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        global $g_crypto_hash;

        // First clean up, so that we don't find expired tokens.
        $this->cleanup();

        $tokenDto = $this->findByToken($token);

        if ($tokenDto === null) {
            return false;
        }

        // Fetch tenant database info.
        $readQuery = "SELECT *
            FROM isys_mandator
            WHERE isys_mandator__id = {$tokenDto->tenantId}
            AND isys_mandator__active = 1
            LIMIT 1;";

        $tenantRow = $this->systemDao->retrieve($readQuery)
            ->get_row();

        if (!$tenantRow) {
            return false;
        }

        // Connect to the tenant database.
        $tenantDao = $this->getTenantDao($tenantRow);

        // Set crypto hash to tenant value instead using global one
        $g_crypto_hash = $tenantRow['isys_mandator__crypto_hash'] ?: $g_crypto_hash;

        // Prepare data to be saved.
        $cleanNewPassword = $tenantDao->convert_sql_text(\isys_helper_crypt::encryptPassword($newPassword));

        // Update the password.
        $updateQuery = "UPDATE isys_cats_person_list
			SET isys_cats_person_list__user_pass = {$cleanNewPassword}
			WHERE isys_cats_person_list__isys_obj__id = {$tokenDto->userId}
            LIMIT 1;";

        if ($tenantDao->update($updateQuery) && $tenantDao->apply_update()) {
            $this->systemDao->update("DELETE FROM isys_reset_password_token
                        WHERE isys_reset_password_token__token = {$this->systemDao->convert_sql_text($tokenDto->token)}");

            $this->systemDao->apply_update();

            return true;
        }

        return false;
    }

    private function __clone()
    {
    }

    /**
     * Will generate a token.
     *
     * @return string
     * @throws \Random\RandomException
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_RANDOM_BYTES));
    }

    /**
     * Will generate a unique and unused token.
     *
     * @return string
     * @throws \isys_exception_dao
     * @throws \isys_exception_database
     */
    private function generateUniqueToken(): string
    {
        $token = $this->generateToken();

        // First clean up, so that we don't find expired tokens.
        $this->cleanup();

        while ($this->findByToken($token) !== null) {
            $token = $this->generateToken();
        }

        return $token;
    }

    /**
     * Create a token in the databse.
     *
     * @param int $userId
     * @param int $tenantId
     *
     * @return string
     * @throws \DateMalformedStringException
     * @throws \isys_exception_dao
     */
    private function createToken(int $userId, int $tenantId, string $ttl = self::TOKEN_TTL): string
    {
        $token = $this->generateUniqueToken();
        $created = (new \DateTime())->format('Y-m-d H:i:s');
        $expires = (new \DateTime())->modify($ttl)
            ->format('Y-m-d H:i:s');

        $createQuery = "INSERT INTO isys_reset_password_token SET
            isys_reset_password_token__token = '{$token}',
            isys_reset_password_token__user_id = {$userId},
            isys_reset_password_token__tenant_id = {$tenantId},
            isys_reset_password_token__created = '{$created}',
            isys_reset_password_token__expires = '{$expires}';";

        $this->systemDao->update($createQuery);
        $this->systemDao->apply_update();

        return $token;
    }

    /**
     * Find active token in the database.
     *
     * @param string $token
     *
     * @return Token|null
     * @throws \isys_exception_database
     */
    private function findByToken(string $token): ?Token
    {
        $cleanToken = $this->systemDao->convert_sql_text($token);
        $findQuery = "SELECT
                isys_reset_password_token__user_id AS userId,
                isys_reset_password_token__tenant_id AS tenantId
            FROM isys_reset_password_token
            WHERE isys_reset_password_token__token = {$cleanToken}
            AND isys_reset_password_token__expires >= NOW()
            LIMIT 1;";

        $row = $this->systemDao->retrieve($findQuery)
            ->get_row();

        if (!$row) {
            return null;
        }

        return new Token($token, (int)$row['userId'], (int)$row['tenantId']);
    }

    /**
     * Delete all expired tokens.
     *
     * @return void
     * @throws \isys_exception_dao
     */
    private function cleanup(): void
    {
        $cleanupQuery = 'DELETE FROM isys_reset_password_token WHERE isys_reset_password_token__expires <= NOW();';

        $this->systemDao->update($cleanupQuery);
        $this->systemDao->apply_update();
    }

    /**
     * Method to retrieve a connected tenant DAO.
     *
     * @param array $tenantData
     *
     * @return \isys_cmdb_dao
     * @throws \Exception
     */
    private function getTenantDao(array $tenantData): \isys_cmdb_dao
    {
        global $g_db_system;

        return new \isys_cmdb_dao(\isys_component_database::get_database(
            $g_db_system["type"],
            $tenantData["isys_mandator__db_host"],
            $tenantData["isys_mandator__db_port"],
            $tenantData["isys_mandator__db_user"],
            \isys_component_dao_mandator::getPassword($tenantData),
            $tenantData["isys_mandator__db_name"]
        ));
    }

    /**
     * Find all active users for given e-mail address.
     *
     * @param string $email
     *
     * @return UserInfo[]
     */
    private function findUsersByEmail(string $email): array
    {
        if (!$this->isActive()) {
            return [];
        }

        $users = [];
        $result = $this->systemDao->retrieve('SELECT * FROM isys_mandator WHERE isys_mandator__active = 1;');

        while ($tenantRow = $result->get_row()) {
            // Skip tenants, that did not activate the feautre.
            if (!$this->isActive((int)$tenantRow['isys_mandator__id'])) {
                continue;
            }

            $tenantDao = $this->getTenantDao($tenantRow);

            $statusNormal = $tenantDao->convert_sql_int(C__RECORD_STATUS__NORMAL);
            $cleanEmail = $tenantDao->convert_sql_text($email);

            // Find active users, with username and matching e-mail address who are allowed to log in.
            $query = "SELECT
                isys_obj__id AS userId,
                isys_cats_person_list__title AS userName
                FROM isys_cats_person_list
                INNER JOIN isys_obj ON isys_obj__id = isys_cats_person_list__isys_obj__id
                WHERE isys_obj__status = {$statusNormal}
                AND isys_cats_person_list__disabled_login = 0
                AND isys_cats_person_list__title IS NOT NULL
                AND TRIM(isys_cats_person_list__title) != ''
                AND isys_cats_person_list__password_reset_email = {$cleanEmail};";

            $userResult = $tenantDao->retrieve($query);

            while ($userRow = $userResult->get_row()) {
                $users[] = new UserInfo((int)$userRow['userId'], $userRow['userName'], (int)$tenantRow['isys_mandator__id'], $tenantRow['isys_mandator__title'], );
            }
        }

        return $users;
    }

    /**
     * Get reset link
     *
     * @param string $token
     *
     * @return string
     */
    private function getResetLink(string $token): string
    {
        return \isys_helper_link::get_base() . "reset-password?token={$token}";
    }

    /**
     * Send reset emails
     *
     * @param string $email
     * @param array  $resetEmailData
     * @param string $ttl
     *
     * @return void
     */
    private function sendResetEmails(string $email, array $resetEmailData, string $ttl): void
    {
        // system smtp settings will be used
        $mailer = new \isys_library_mail();
        $mailer->set_charset('UTF-8')
            ->set_backend(isys_library_mail::C__BACKEND__SMTP)
            ->set_subject("Password reset request for {$email}")
            ->set_body($this->buildResetEmail($email, $resetEmailData, $ttl))
            // we need to set the content type to HTML after body
            // otherwise nl2br will be applied
            ->set_content_type(isys_library_mail::C__CONTENT_TYPE__HTML);

        $mailer->addAddress($email);
        $mailer->send();
    }

    /**
     * Build reset email
     *
     * @param string $email
     * @param array  $resetEmailData
     * @param string $ttl
     *
     * @return string
     */
    private function buildResetEmail(string $email, array $resetEmailData, string $ttl): string
    {
        $template = isys_component_template::instance();

        return $template->assign('email', $email)
            ->assign('resetEmailData', $resetEmailData)
            ->assign('ttl', $ttl)
            ->fetch('email/password_reset.tpl');
    }

    private function __construct(\isys_component_database $systemDatabase)
    {
        $this->systemDao = new \isys_cmdb_dao($systemDatabase);
        $this->logger = Logger::factory('password_reset', BASE_DIR . 'log/password_reset.log');
    }
}
