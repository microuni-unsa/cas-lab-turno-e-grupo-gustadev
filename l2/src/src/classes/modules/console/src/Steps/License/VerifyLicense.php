<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps\License;

use DateTime;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;
use idoit\Module\Console\Steps\Step;
use idoit\Module\License\LicenseService;
use isys_component_database;

class VerifyLicense implements Step
{
    private isys_component_database $database;
    private int $tenantId;
    private bool $considerBuffer;
    private LicenseService $licenseService;

    public function __construct(isys_component_database $database, int $tenantId, bool $considerBuffer, LicenseService $licenseService)
    {
        $this->database = $database;
        $this->tenantId = $tenantId;
        $this->considerBuffer = $considerBuffer;
        $this->licenseService = $licenseService;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Verify license for tenant with ID ' . $this->tenantId;
    }

    /**
     * Process the work
     *
     * @param Messages $messages
     *
     * @return mixed
     * @throws \Exception
     */
    public function process(Messages $messages)
    {
        // Check if the given tenant actually exists.
        $systemDao = new \isys_cmdb_dao($this->database);

        $tenantExists = 1 == $systemDao
            ->retrieve("SELECT COUNT(1) AS cnt FROM isys_mandator WHERE isys_mandator__id = {$this->tenantId};")
            ->get_row_value('cnt');

        if (!$tenantExists) {
            $messages->addMessage(new StepMessage($this, "given tenant does not exist", ErrorLevel::ERROR));
            return false;
        }

        if ($this->licenseService->isTenantLicensed($this->tenantId) || $this->licenseService->isEvaluation()) {
            $messages->addMessage(new StepMessage($this, "is licensed", ErrorLevel::INFO));
            return true;
        }

        if (!$this->considerBuffer) {
            $messages->addMessage(new StepMessage($this, "is NOT licensed", ErrorLevel::ERROR));
            return false;
        }

        // Check if we are still within 30 days...
        $query = "SELECT isys_settings__value AS `value`
            FROM isys_settings
            WHERE isys_settings__key = 'system.start-of-end'
            AND isys_settings__isys_mandator__id = {$this->tenantId}
            LIMIT 1;";

        // Here we'll either get NULL or a date value.
        $startOfEnd = $systemDao->retrieve($query)->get_row_value('value');

        if ($startOfEnd === null || strtotime($startOfEnd ?? '') === false) {
            $messages->addMessage(new StepMessage($this, "is NOT licensed", ErrorLevel::ERROR));
            return false;
        }

        // Did the end start less than 30 days ago? Then the license is still good.
        $licenseValid = (new DateTime($startOfEnd))->diff(new DateTime())->days <= \isys_module_licence::LICENSE_BUFFER_DAYS;

        if ($licenseValid) {
            $messages->addMessage(new StepMessage($this, "buffer is active", ErrorLevel::NOTIFICATION));
            return true;
        }

        $messages->addMessage(new StepMessage($this, "buffer is exceeded", ErrorLevel::ERROR));
        return false;
    }
}
