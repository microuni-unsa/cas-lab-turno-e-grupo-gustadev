<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     i-doit
 * @subpackage  Modules
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @version     1.1
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Download\DownloadType;
use idoit\Component\Upload\Types\FileCategory;
use idoit\Component\Upload\Types\ObjectImage;
use idoit\Component\Upload\Types\ObjectTypeIcon;
use idoit\Component\Upload\Types\ObjectTypeImage;
use idoit\Model\QuickInfo;
use idoit\Module\Cmdb\Event\EventListenerCollection;

if (class_exists('\idoit\Psr4AutoloaderClass')) {
    \idoit\Psr4AutoloaderClass::factory()
        ->addNamespace('idoit\Module\Cmdb', __DIR__ . '/src/');
}

$session = isys_application::instance()->container->get('session');

if ($session->is_logged_in()) {
    // Connect search signals.
    idoit\Module\Cmdb\Search\Index\Signals::instance()
        ->connect();

    // @see ID-8820 Implement specific logic to document purged networks.
    $database = isys_application::instance()->container->get('database');

    isys_application::instance()->container->get('signals')
        ->connect('mod.cmdb.beforeRankRecord', [isys_application::instance()->container->get('cmdb_dao'), 'beforeObjectPurge'])
        ->connect('mod.cmdb.objectDeleted', [isys_cmdb_dao_category_g_ip::instance($database), 'onObjectPurge'])
        // Unique-Handling for ips.
        ->connect('mod.cmdb.afterObjectRank', ['isys_cmdb_dao_category_g_ip', 'unique_handling'])
        // @see ID-5543
        ->connect('mod.cmdb.afterObjectRank', ['isys_cmdb_dao_category_g_cluster_service', 'assignmentStatusTransition'])
        ->connect('mod.cmdb.afterObjectRank', ['isys_cmdb_dao_connection', 'unidirectionalConnectionRanking'])
        // Handle authentication rules which are related to the ranked object.
        ->connect('mod.cmdb.afterObjectRank', ['isys_auth_dao_cmdb', 'objectRelatedAuthenticationRules'])
        // @see ID-5599  Automatic inventory number creation.
        ->connect('mod.cmdb.objectCreated', [isys_cmdb_dao_category_g_accounting::instance($database), 'signal_auto_inventory_no'])
        // @see ID-8634 create entries for specific categories person, organization and person group
        ->connect('mod.cmdb.objectCreated', [isys_cmdb_dao_category_s_person_master::instance($database), 'signalObjectCreated'])
        ->connect('mod.cmdb.objectCreated', [isys_cmdb_dao_category_s_organization_master::instance($database), 'signalObjectCreated'])
        ->connect('mod.cmdb.objectCreated', [isys_cmdb_dao_category_s_person_group_master::instance($database), 'signalObjectCreated'])
        // @see ID-6913  Update quickinfo after a object has changed.
        ->connect('mod.cmdb.objectChanged', function ($objectIds = null, $changedBy = null) use ($database) {
            if ($objectIds !== null) {
                QuickInfo::instance($database)->deleteCache($objectIds);
            }

            // @todo  Should `isys_lock` also be cleared here?
        });

    if (isys_application::instance()->container->get('settingsTenant')->get('cmdb.release-ip-on-archive-delete', 0)) {
        isys_application::instance()->container->get('signals')
            ->connect('mod.cmdb.afterObjectRank', ['isys_module_cmdb', 'detach_ip_address']);
    }

    // Connect a new route to match old "/cmdb/object/123" URLs.
    isys_request_controller::instance()
        ->addModuleRoute('GET', '/cmdb/object/[i:id]', 'cmdb', 'ObjectController')
        ->addModuleRoute('POST', '/cmdb/browse-location/[i:id]', 'cmdb', 'BrowseLocation', 'getObjectData')
        ->addModuleRoute('POST', '/cmdb/browse/get-object-data', 'cmdb', 'Browse', 'getObjectData')
        ->addModuleRoute('POST', '/cmdb/browse/get-selection-data', 'cmdb', 'Browse', 'getSelectionData')
        ->addModuleRoute('POST', '/cmdb/browse/[s:method]/[**:id]', 'cmdb', 'Browse')
        ->addModuleRoute('POST', '/cmdb/list-config/[s:method]/[**:id]', 'cmdb', 'ListConfig')
        ->addModuleRoute('GET', '/setting', 'cmdb', 'Setting', 'get')
        ->addModuleRoute('POST', '/setting', 'cmdb', 'Setting', 'set');

    $tenantId = 0;

    if (isys_application::instance()->tenant !== null) {
        $tenantId = (int) isys_application::instance()->tenant->id;
    }

    if ($tenantId === 0) {
        $tenantId = (int) $session->get_mandator_id();
    }

    isys_application::instance()->container->get('upload')
        ->registerUploadType('cmdb.object-type-icon', new ObjectTypeIcon($tenantId))
        ->registerUploadType('cmdb.object-type-image', new ObjectTypeImage($tenantId))
        ->registerUploadType('cmdb.object-image', new ObjectImage())
        ->registerUploadType('cmdb.global-file-category', new FileCategory());

    isys_application::instance()->container->get('download')
        ->registerDownloadType('cmdb.object-image', new DownloadType([isys_cmdb_dao_category_g_image::class, 'getDownloadPath']))
        ->registerDownloadType('cmdb.global-file-category', new DownloadType([isys_cmdb_dao_category_g_file::class, 'getDownloadPath']));

    // @see ID-10672 Register Listeners
    $listeners = [
        isys_cmdb_dao_category_s_organization_master::class => isys_cmdb_dao_category_s_organization_master::getEventListeners(),
        isys_cmdb_dao_category_s_person_group::class => isys_cmdb_dao_category_s_person_group::getEventListeners(),
        isys_cmdb_dao_category_s_person_master::class => isys_cmdb_dao_category_s_person_master::getEventListeners(),
        isys_cmdb_dao_category_g_contact::class => isys_cmdb_dao_category_g_contact::getEventListeners(),
    ];

    /* @var EventListenerCollection $eventCollection */
    foreach ($listeners as $class => $eventCollection) {
        $events = $eventCollection->getEventListeners();
        foreach ($events as $event) {
            $class = $event->getClassName();
            isys_application::instance()->container->get('event_dispatcher')
                ->addListener($event->getListenToEvent(), [$class::instance($database), $event->getMethod()]);
        }
    }
}
