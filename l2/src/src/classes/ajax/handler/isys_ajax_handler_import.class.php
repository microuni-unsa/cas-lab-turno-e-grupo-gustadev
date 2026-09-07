<?php

use idoit\Component\Interval\Config;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\IdoitConsoleApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * AJAX handler for the Interval component
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_ajax_handler_import extends isys_ajax_handler
{
    /**
     * Init method.
     */
    public function init()
    {
        // We set the header information because we don't accept anything than JSON.
        header('Content-Type: application/json');

        $return = [
            'success' => true,
            'message' => null,
            'data'    => null
        ];

        try {
            $application = new IdoitConsoleApplication();
            $application->setAutoExit(false);

            $file = $_POST['file'];
            $type = $_POST['type'];

            if (empty($file) || !file_exists($file)) {
                throw new isys_exception_general('The given file does not seem to exist.');
            }

            $typeMapping = [
                'cmdb'       => 'import-xml',
                'hinventory' => 'import-hinventory'
            ];

            if (!isset($typeMapping[$type])) {
                throw new isys_exception_general('The given import type "' . $type . '" does not exist.');
            }

            // Default params
            $commandParams = [
                'command'      => $typeMapping[$type],
                '--importFile' => $file,
                '--user'       => 'loginBefore',
                '--password'   => 'loginBefore',
                '--tenantId'   => 'loginBefore'
            ];

            if ($type === 'hinventory') {
                $commandParams['--objectType'] = $_POST['obj_type'];
                $commandParams['--objectId'] = $_POST['object_id__HIDDEN'];

                if ($_POST['force']) {
                    $commandParams['--force'] = true;
                }
            }

            /** @var $command AbstractCommand */
            $command = $application->find($typeMapping[$type]);

            if (method_exists($command, 'validateParameters')) {
                // Handle specific command parameters in their own command
                if (!$command->validateParameters($commandParams)) {
                    throw new isys_exception_general('The given parameters are not valid for this import.');
                }
            }

            $command->setSession(isys_application::instance()->container->get('session'));
            $command->setContainer(isys_application::instance()->container);
            $command->setAuth(isys_auth_system::instance());

            // Not sure why, but the callback is necessary.
            ob_start(function (string $buffer, int $phase): string {
                return '';
            });

            $output = new BufferedOutput();

            $application->run(new ArrayInput($commandParams), $output);

            $return['data'] = nl2br(implode("\n", [ob_get_contents(), $output->fetch()]));

            ob_end_flush();
        } catch (Exception $e) {
            $return['success'] = false;
            $return['message'] = $e->getMessage();
        }

        echo isys_format_json::encode($return);
        $this->_die();
    }
}
