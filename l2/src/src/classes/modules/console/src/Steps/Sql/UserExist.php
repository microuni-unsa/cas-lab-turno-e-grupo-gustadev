<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps\Sql;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;

class UserExist extends SqlStep
{
    private $userToCheck = null;

    public function __construct($host, $username, $password, $port, $userToCheck)
    {
        parent::__construct($host, $username, $password, '', $port);

        $this->userToCheck = $userToCheck;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'User exists: ' . $this->userToCheck;
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
            $messages->addMessage(new StepMessage($this, 'Cannot connect to Sql', ErrorLevel::FATAL));

            return false;
        }

        return $connection->query("SELECT 1 FROM mysql.user WHERE user = '".$this->userToCheck."'")->num_rows === 1;
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
        return true;
    }
}
