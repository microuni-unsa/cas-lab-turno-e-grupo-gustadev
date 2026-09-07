<?php

namespace idoit\Module\System\Controller;

use Exception;
use idoit\Controller\Base;
use idoit\Tree\Node;
use isys_application as Application;
use isys_component_tree as Tree;
use isys_controller as Controller;
use isys_module as Module;
use isys_register as Register;

/**
 * i-doit cmdb controller
 *
 * @package     Modules
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Main extends Base implements Controller
{
    /**
     * @var Module
     */
    protected $module;

    /**
     * Constructor method.
     *
     * @param  Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @param   Register    $request
     * @param   Application $application
     *
     * @return  \idoit\View\Renderable|void
     */
    public function handle(Register $request, Application $application)
    {
        // nothing to do
    }

    public function dao(Application $p_application)
    {
        // nothing to do
    }

    /**
     * Build the left tree.
     *
     * @param   Register    $request
     * @param   Application $application
     * @param   Tree        $tree
     *
     * @return  Node
     * @throws  Exception
     */
    public function tree(Register $request, Application $application, Tree $tree)
    {
        // nothing to do
    }
}
