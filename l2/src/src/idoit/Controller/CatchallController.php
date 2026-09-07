<?php

namespace idoit\Controller;

use idoit\Component\Provider\DiInjectable;
use idoit\Context\Context;
use idoit\Dispatcher\NavbarDispatcher;
use idoit\Tree\TreeProcessor;
use idoit\View\Renderable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * i-doit Base Controller
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CatchallController extends Base
{
    /**
     * @param ContainerInterface $container
     *
     * @return mixed
     */
    public static function factory(ContainerInterface $container): mixed
    {
        return (new static())->setDi($container);
    }

    /**
     * @param \isys_register $p_request
     *
     * @return void
     * @throws \Exception
     * @deprecated
     */
    final public function handle(\isys_register $p_request): void
    {
        Context::instance()->setOrigin(Context::ORIGIN_GUI);

        // @todo remove this legacyRequest in future
        $this->getDi()->set('legacyRequest', $p_request);

        if (!isset($p_request->module)) {
            throw new \Exception('Request error for request ' . \isys_request_controller::instance()->path() . ' : ' . var_export($p_request, true));
        }

        // Get module instance
        $l_module_id = $this->getDi()->get('moduleManager')->is_installed($p_request->module);

        if (!$l_module_id) {
            return;
        }

        // Load and start the module.
        $this->getDi()->get('application')->module = $this->getDi()->get('moduleManager')->load($l_module_id, $p_request);

        // Preformat action to match class naming conventions.
        $p_request->action = str_replace(' ', '', ucwords(str_replace('-', ' ', $p_request->action ?? '')));

        $loadClassName = 'idoit\\Module\\' . str_replace(' ', '', ucfirst(str_replace('_', ' ', $p_request->module))) . '\\Controller\\';

        if (trim($p_request->action) !== '') {
            $loadClassName .= $p_request->action;
        } else {
            $loadClassName .= 'Main';
        }

        // Namespaces should be CamelCase.
        if (!class_exists($loadClassName)) {
            $loadClassName = 'idoit\\Module\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $p_request->module))) . '\\Controller\\';
            if (trim($p_request->action) !== '') {
                $loadClassName .= $p_request->action;
            } else {
                $loadClassName .= 'Main';
            }
        }

        // Call 404 handler
        if (!class_exists($loadClassName)) {
            $this->getDi()->get('application')->error404($p_request);
            return;
        }

        /** @var $l_controller \isys_controller */
        $l_controller = new $loadClassName($this->getDi()->get('application')->module);

        if (classUsesTraitRecursive($l_controller, DiInjectable::class)) {
            $l_controller->setContainer($this->container);
        }

        // Call controller's pre route function
        if (method_exists($l_controller, 'pre')) {
            $l_controller->pre($p_request, $this);
        }

        if (isset($p_request->method) && $p_request->method !== '' && method_exists($l_controller, $p_request->method)) {
            // Call controller's handler
            //@todo: remove sending $this on future
            $l_view = call_user_func([$l_controller, $p_request->method], $p_request, $this);
        } else {
            // Call controller's main handler
            $l_view = $l_controller->handle($p_request, $this->getDi()->get('application'));
        }

        // If controller is a NavbarHandable, also call onNew, onSave etc. events
        if ($l_controller instanceof NavbarHandable) {
            $l_view = NavbarDispatcher::factory($this->getDi())
                ->dispatch($l_controller, $p_request->get('POST')->get(C__GET__NAVMODE));
        }

        // Process main tree
        TreeProcessor::factory($this->getDi())->process($l_controller, $p_request);

        // Call controller's post route funciton
        if (method_exists($l_controller, 'post')) {
            $l_controller->post($p_request, $this);
        }

        // Process and Render the view, if view is renderable.
        if ($l_view && $l_view instanceof Renderable) {
            $l_view->process(
                $this->getDi()->get('application')->module,
                $this->getDi()->get('template'),
                $l_controller->dao($this->getDi()->get('application'))
            );

            $l_view->render();
        }
    }
}
