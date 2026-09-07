<?php

namespace idoit\Legacy;

use idoit\Component\ConstantManager;
use idoit\Component\Provider\DiFactory;
use isys_application;

/**
 * i-doit
 *
 * Module loader for legacy (isys_) modules
 *
 * @package     i-doit
 * @subpackage  Core
 * @author      Dennis Stücken <dstuecken@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ModuleLoader
{
    use DiFactory;

    /**
     * Module Boot Loader
     *
     * @param int            $moduleId
     * @param \isys_register $request
     */
    public function boot($moduleId, $request)
    {
        global $g_modman;
        if (isset($g_modman) && is_object($g_modman)) {
            // Check for access to the module.
            if (is_numeric($moduleId)) {
                try {

                    // Set module instance to \isys_application::$module.
                    $this->getDi()->get('application')->module = $g_modman->load($moduleId, $request);
                } catch (\Exception $e) {
                    $this->getDi()
                        ->get('notify')
                        ->error($e->getMessage(), ['sticky' => true, 'width' => '400px']);
                    $this->getDi()->get('logger')->error($e->getMessage() . ' (' . str_replace($this->getDi()->get('application')->app_path . '/', '', $e->getFile()) . ':' .
                        $e->getLine() . ')');
                }
            } else {
                // Doing a logout to have a clean start for the next request.
                $this->getDi()->get('session')->logout();

                if (defined("C__MODULE__CMDB") && is_numeric(C__MODULE__CMDB)) {
                    die("Error: Module ID not numeric. Check your request or constant cache.");
                } else {
                    // Deleting constant cache
                    /** @var ConstantManager $cm */
                    $cm = isys_application::instance()->container->get('constant_manager');
                    $cm->deleteTenantCacheFile();

                    die("Error: Module ID not numeric. Your constant cache is not loaded!");
                }
            }
        }
    }
}
