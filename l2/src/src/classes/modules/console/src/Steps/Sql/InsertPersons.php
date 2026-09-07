<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Denis Koroliov <dkorolov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps\Sql;

use idoit\Component\PasswordReset\PasswordResetService;
use idoit\Component\PasswordReset\UserInfo;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use isys_helper_install;

class InsertPersons extends SqlStep
{
    private $created = false;

    private string $dbName;
    private ?string $loginUser;
    private ?string $loginPassword;

    private ?string $recoveryEmail;

    private bool $sendEmail = false;

    public function __construct(
        $host,
        $rootName,
        $rootPassword,
        $db,
        $port,
        ?string $loginUser = null,
        ?string $loginPassword = null,
        ?string $recoveryEmail = null,
        bool $sendMail = false
    ) {
        parent::__construct($host, $rootName, $rootPassword, $db, $port);

        // Seems like the autoloader did not find the class.
        if (!class_exists(isys_helper_install::class)) {
            global $g_absdir;

            require_once $g_absdir . '/src/classes/helper/isys_helper_install.php';
        }

        $this->dbName = $db;
        // @see ID-10785 Implement additional login user and password.
        $this->loginUser = $loginUser;
        $this->loginPassword = $loginPassword;
        $this->recoveryEmail = $recoveryEmail;
        $this->sendEmail = $sendMail;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function process(Messages $messages)
    {
        $connection = $this->createConnection();
        if ($connection->connect_error || $connection->error) {
            $messages->addMessage(new StepMessage($this, 'Cannot connect to DB', ErrorLevel::FATAL));

            return false;
        }

        if ($this->loginUser !== null && !$this->loginPassword) {
            $messages->addMessage(new StepMessage($this, 'You need to provide a login user and password: ', ErrorLevel::ERROR));
            return false;
        }

        if ($this->loginUser !== null && $this->loginPassword !== null && (trim($this->loginUser) === '' || trim($this->loginPassword) === '')) {
            $messages->addMessage(new StepMessage($this, 'You need to provide a valid string for both login user and password: ', ErrorLevel::ERROR));
            return false;
        }

        if ($connection->query(isys_helper_install::generatePersonsListDataQuery($this->loginUser, $this->loginPassword, $this->recoveryEmail))) {
            $messages->addMessage(new StepMessage($this, 'Users successfully added!', ErrorLevel::INFO));

            if ($this->recoveryEmail) {
                $messages->addMessage(new StepMessage($this, "Recovery e-mail successfully set for user {$this->loginUser}", ErrorLevel::INFO));
            }
            if ($this->sendEmail) {
                /** @var PasswordResetService $passwordResetService */
                $passwordResetService = \isys_application::instance()->container->get('reset_password');
                // Get user information
                $user = $connection->query("SELECT * FROM isys_cats_person_list
                        WHERE isys_cats_person_list__password_reset_email = '{$this->recoveryEmail}'")
                    ->fetch_assoc();

                // Get tenant information
                /** @var \isys_component_database $tenantId */
                $tenant = \isys_application::instance()->container->get('database_system')
                    ->query("SELECT isys_mandator__id, isys_mandator__title FROM isys_mandator WHERE isys_mandator__db_name = '{$this->dbName}'")
                    ->fetch_assoc();
                $userInfo = new UserInfo(
                    $user['isys_cats_person_list__isys_obj__id'],
                    $user['isys_cats_person_list__title'],
                    $tenant['isys_mandator__id'],
                    $tenant['isys_mandator__title']
                );
                $passwordResetService->requestInitialPasswordSet($userInfo, $this->recoveryEmail);
            }
            $this->created = true;
        } else {
            $messages->addMessage(new StepMessage($this, "Can't add users to database. ", ErrorLevel::FATAL));
            return false;
        }

        return true;
    }

    /**
     * Undo the work
     *
     * @param Messages $messages
     *
     * @return mixed
     */
    public function undo(Messages $messages)
    {
        if (!$this->created) {
            return true;
        }
        $messages->addMessage(new StepMessage($this, 'Removing persons from database. ', ErrorLevel::INFO));
        $connection = $this->createConnection();
        if ($connection->error) {
            $messages->addMessage(new StepMessage($this, 'Cannot connect to DB', ErrorLevel::FATAL));

            return false;
        }

        if ($connection->query("DELETE FROM isys_cats_person_list") === false) {
            $messages->addMessage("Cannot delete users");

            return false;
        }

        return true;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Inserting persons to database';
    }
}
