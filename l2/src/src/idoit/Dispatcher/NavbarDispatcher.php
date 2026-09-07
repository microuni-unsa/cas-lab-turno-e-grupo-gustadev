<?php

namespace idoit\Dispatcher;

use idoit\Component\Provider\DiFactory;
use idoit\View\Renderable;

/**
 * i-doit Navbar dispatcher
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class NavbarDispatcher
{
    use DiFactory;

    /**
     * @param \isys_controller $p_controller
     * @param int|null         $mode
     *
     * @return Renderable|null
     */
    public function dispatch(\isys_controller $p_controller, ?int $mode = null): ?Renderable
    {
        switch ($mode) {
            case C__NAVMODE__NEW:
                $eventFunction = 'onNew';
                break;
            case C__NAVMODE__PRINT:
                $eventFunction = 'onPrint';
                break;
            case C__NAVMODE__PURGE:
                $eventFunction = 'onPurge';
                break;
            case C__NAVMODE__DELETE:
                $eventFunction = 'onDelete';
                break;
            case C__NAVMODE__ARCHIVE:
                $eventFunction = 'onArchive';
                break;
            case C__NAVMODE__QUICK_PURGE:
                $eventFunction = 'onQuickPurge';
                break;
            case C__NAVMODE__RECYCLE:
                $eventFunction = 'onRecycle';
                break;
            case C__NAVMODE__DUPLICATE:
                $eventFunction = 'onDuplicate';
                break;
            case C__NAVMODE__RESET:
                $eventFunction = 'onReset';
                break;
            case C__NAVMODE__EDIT:
                $eventFunction = 'onEdit';
                break;
            case C__NAVMODE__CANCEL:
                $eventFunction = 'onCancel';
                break;
            case C__NAVMODE__SAVE:
                $eventFunction = 'onSave';
                break;
            default:
                $eventFunction = 'onDefault';
                break;
        }

        if ($eventFunction && method_exists($p_controller, $eventFunction)) {
            return $p_controller->$eventFunction($this->getDi()->get('legacyRequest'), $this->getDi()->get('application'));
        }

        return null;
    }
}
