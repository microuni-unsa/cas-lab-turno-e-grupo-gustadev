<?php
/**
 * @package     i-doit
 * @subpackage
 * @author      Selcuk Kekec <skekec@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps;

use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\Message\Messages;
use idoit\Module\Console\Steps\Message\StepMessage;

class SetSettingsStep implements Step
{
    private string $settingsMap;
    private bool $force;

    public function __construct(string $settingsMap, bool $force = false)
    {
        $this->settingsMap = $settingsMap;
        $this->force = $force;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Set system settings';
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
        if (!\isys_format_json::is_json($this->settingsMap)) {
            return false;
        }
        $map = \isys_format_json::decode($this->settingsMap, true);
        $definitions = \isys_settings::getRawDefinition();

        foreach ($map as $setting => $value) {
            if (!$definitions[$setting]) {
                if (!$this->force) {
                    $messages->addMessage(new StepMessage($this, "'{$setting}' does not exist and will be skipped...", ErrorLevel::NOTIFICATION));
                    continue;
                }

                $messages->addMessage(new StepMessage($this, "Non-existing '{$setting}' forcefully set to '{$value}'", ErrorLevel::INFO));
            } else {
                $messages->addMessage(new StepMessage($this, "'{$setting}' set to '{$value}'", ErrorLevel::INFO));
            }

            \isys_settings::set($setting, $value);
        }

        return true;
    }
}
