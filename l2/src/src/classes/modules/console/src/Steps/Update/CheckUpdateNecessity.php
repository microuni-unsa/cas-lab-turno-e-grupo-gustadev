<?php

namespace idoit\Module\Console\Steps\Update;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use isys_application;
use isys_update_files;

/**
 * Class CopyUpdateFiles
 */
class CheckUpdateNecessity implements Step
{
    /**
     * @var bool Skip necessity check?
     */
    private bool $checkVersions;

    /**
     * FileCopy constructor.
     */
    public function __construct(bool $checkVersions = false)
    {
        $this->checkVersions = $checkVersions;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Check if update is necessary';
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return bool
     */
    public function process(Messages $messages)
    {
        global $g_product_info;
        $db = \isys_application::instance()->container->get('database_system');
        $res = $db->fetch_row($db->query('SELECT isys_db_init__value FROM isys_db_init WHERE isys_db_init__key = \'version\''));

        if (!isset($res[0]) || !is_numeric($res[0])) {
            throw new \Exception('Unable to retrieve db version from database.');
        }
        $dbVersion = (int)$res[0];
        $codeVersion = (int)isys_application::instance()->info->get('version');

        if ($codeVersion > $dbVersion) {
            $messages->addMessage(new StepMessage($this, 'Old database version detected - update is necessary.', ErrorLevel::INFO));
            return true;
        }

        if (!$this->checkVersions) {
            $messages->addMessage(new StepMessage($this, 'Database- and code version are equal - unnecessary update will be triggered (skip these updates by using --check-versions option.', ErrorLevel::INFO));
            return true;
        }

        $messages->addMessage(new StepMessage($this, 'Database- and code version are equal - unnecessary update will be skipped.', ErrorLevel::INFO));
        return false;
    }
}
