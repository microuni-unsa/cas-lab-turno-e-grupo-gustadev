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

class UserCredentialsCorrect extends SqlStep
{
    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'User credentials correct?';
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
        try {
            $connection = $this->createConnection();
            if ($connection->connect_error || $connection->error) {
                $messages->addMessage(new StepMessage($this, 'Cannot connect to Sql', ErrorLevel::FATAL));

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $messages->addMessage(new StepMessage($this, 'Please check credentials for mysql user ' . $this->getUsername(), ErrorLevel::FATAL));
            return false;
        }
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
