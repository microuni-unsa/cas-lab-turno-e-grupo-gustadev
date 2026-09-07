<?php
/**
 *
 *
 * @see         ID-10810
 * @package     i-doit
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Steps;

use Composer\Semver\Semver;
use idoit\Module\Console\Steps\Message\ErrorLevel;

class VersionAndRevisionCheck extends Check
{
    private array $currentState;
    private array $targetState;

    /**
     * @param array  $currentState
     * @param int    $errorLevel
     */
    public function __construct(string $xmlFilePath, array $currentState, int $errorLevel = ErrorLevel::INFO)
    {
        $xml = simplexml_load_file($xmlFilePath, 'SimpleXMLElement', LIBXML_NOCDATA);

        $this->currentState = [
            'version' => (string)$currentState['version'],
            'revision' => (string)$currentState['revision'],
            'required_version' => (string)$currentState['required_version'],
            'required_revision' => (string)$currentState['required_revision']
        ];

        $this->targetState = [
            'version' => (string)$xml->info->version,
            'revision' => (string)$xml->info->revision,
            'required_version' => (string)$xml->info->requirement->version,
            'required_revision' => (string)$xml->info->requirement->revision,
        ];

        $this->level = $errorLevel;
    }

    /**
     * Get name of the step
     *
     * @return string
     */
    public function getName()
    {
        return 'Verify that the version and revision is allowed to be updated to';
    }

    /**
     * Check the requirements of the step
     *
     * @return mixed
     */
    public function check()
    {
        // The current version needs to be equal or above the required version.
        if (Semver::satisfies($this->currentState['version'], '< ' . $this->targetState['required_version'])) {
            return false;
        }

        // The current revision needs to be equal or above the required revision.
        if (Semver::satisfies($this->currentState['revision'], '< ' . $this->targetState['required_revision'])) {
            return false;
        }

        // The current version needs to be equal or lower than the target version.
        if (Semver::satisfies($this->currentState['version'], '> ' . $this->targetState['version'])) {
            return false;
        }

        // The current revision needs to be equal or lower than the target revision.
        if (Semver::satisfies($this->currentState['revision'], '> ' . $this->targetState['revision'])) {
            return false;
        }

        return true;
    }
}
