<?php

namespace idoit\Module\System\Cleanup;

use Exception;
use isys_cmdb_dao;
use isys_component_template_language_manager;

/**
 * Class MigrateOldObjectTypeIcons
 *
 * @package idoit\Module\System\Cleanup
 */
class MigrateOldObjectTypeIcons extends AbstractCleanup
{
    /**
     * Method for starting the cleanup process.
     */
    public function process()
    {
        \isys_module_system::getAuth()->check(\isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        /** @var isys_cmdb_dao $dao */
        $dao = $this->container->get('cmdb_dao');
        /** @var isys_component_template_language_manager $dao */
        $language = $this->container->get('language');

        $mapping = [
            'C__CMDB__OBJTYPE__CABLE_TRAY'     => 'images/axialis/hardware-network/power-plug.svg',
            'C__CMDB__OBJTYPE__CONDUIT'        => 'images/axialis/development/threads-filled.svg',
            'C__OBJECT_TYPE__GROUP'            => 'images/axialis/development/module.svg',
            'C__OBJTYPE__ACCESS_POINT'         => 'images/axialis/hardware-network/router-1.svg',
            'C__OBJTYPE__AIRCRAFT'             => 'images/axialis/transportation/vehicle-plane.svg',
            'C__OBJTYPE__AIR_CONDITION_SYSTEM' => 'images/axialis/industry-manufacturing/turbine-filled.svg',
            'C__OBJTYPE__AMPLIFIER'            => 'images/axialis/hardware-network/drive.svg',
            'C__OBJTYPE__APPLIANCE'            => 'images/axialis/hardware-network/computer-laptop-1.svg',
            'C__OBJTYPE__APPLICATION'          => 'images/axialis/development/application-empty.svg',
            'C__OBJTYPE__BLADE_CHASSIS'        => 'images/axialis/hardware-network/blade-chassis.svg',
            'C__OBJTYPE__BLADE_SERVER'         => 'images/axialis/database/server.svg',
            'C__OBJTYPE__BUILDING'             => 'images/axialis/construction/building-9.svg',
            'C__OBJTYPE__CABLE'                => 'images/axialis/hardware-network/power-plug.svg',
            'C__OBJTYPE__CELL_PHONE_CONTRACT'  => 'images/axialis/hardware-network/phone-3.svg',
            'C__OBJTYPE__CITY'                 => 'images/axialis/construction/buildings-3.svg',
            'C__OBJTYPE__CLIENT'               => 'images/axialis/hardware-network/computer-3.svg',
            'C__OBJTYPE__CLUSTER'              => 'images/axialis/hardware-network/servers.svg',
            'C__OBJTYPE__CLUSTER_SERVICE'      => 'images/axialis/basic/windows.svg',
            'C__OBJTYPE__CLUSTER_VRRP_HSRP'    => 'images/axialis/basic/windows.svg',
            'C__OBJTYPE__CONTAINER'            => 'images/axialis/cad/select.svg',
            'C__OBJTYPE__CONVERTER'            => 'images/axialis/hardware-network/power-plug.svg',
            'C__OBJTYPE__COUNTRY'              => 'images/axialis/transportation/location-map.svg',
            'C__OBJTYPE__DATABASE_INSTANCE'    => 'images/axialis/database/document-database.svg',
            'C__OBJTYPE__DATABASE_SCHEMA'      => 'images/axialis/database/document-database.svg',
            'C__OBJTYPE__DBMS'                 => 'images/axialis/database/server-database.svg',
            'C__OBJTYPE__DISTRIBUTION_BOX'     => 'images/axialis/hardware-network/electronic-circuit.svg',
            'C__OBJTYPE__EMERGENCY_PLAN'       => 'images/axialis/security/red-siren.svg',
            'C__OBJTYPE__ENCLOSURE'            => 'images/axialis/database/data.svg',
            'C__OBJTYPE__EPS'                  => 'images/axialis/construction/electricity-filled.svg',
            'C__OBJTYPE__ESC'                  => 'images/axialis/basic/light-on-filled.svg',
            'C__OBJTYPE__FC_SWITCH'            => 'images/axialis/hardware-network/drive.svg',
            'C__OBJTYPE__FILE'                 => 'images/axialis/documents-folders/document-type-binary.svg',
            'C__OBJTYPE__GENERIC_TEMPLATE'     => 'images/axialis/development/addin-office.svg',
            'C__OBJTYPE__HOST'                 => 'images/axialis/hardware-network/drive.svg',
            'C__OBJTYPE__IT_SERVICE'           => 'images/axialis/spreadsheet/charts-doughnut-color-filled.svg',
            'C__OBJTYPE__KRYPTO_CARD'          => 'images/axialis/security/key-card.svg',
            'C__OBJTYPE__KVM_SWITCH'           => 'images/axialis/hardware-network/storage-network.svg',
            'C__OBJTYPE__LAYER2_NET'           => 'images/axialis/web-email/cloud-computer.svg',
            'C__OBJTYPE__LAYER3_NET'           => 'images/axialis/web-email/cloud-computer-filled.svg',
            'C__OBJTYPE__LICENCE'              => 'images/axialis/basic/key-filled.svg',
            'C__OBJTYPE__LOCATION_GENERIC'     => 'images/axialis/construction/house-4.svg',
            'C__OBJTYPE__MAINTENANCE'          => 'images/axialis/imaging/tool-pen-1.svg',
            'C__OBJTYPE__MIDDLEWARE'           => 'images/axialis/imaging/layers.svg',
            'C__OBJTYPE__MIGRATION_OBJECT'     => 'images/axialis/development/module.svg',
            'C__OBJTYPE__MONITOR'              => 'images/axialis/hardware-network/screen-2.svg',
            'C__OBJTYPE__NET_ZONE'             => 'images/axialis/web-email/cloud-application.svg',
            'C__OBJTYPE__OPERATING_SYSTEM'     => 'images/axialis/development/application-console.svg',
            'C__OBJTYPE__ORGANIZATION'         => 'images/axialis/construction/building-5.svg',
            'C__OBJTYPE__PARALLEL_RELATION'    => 'images/axialis/user-interface/fullscreen.svg',
            'C__OBJTYPE__PATCH_PANEL'          => 'images/axialis/hardware-network/storage-network.svg',
            'C__OBJTYPE__PDU'                  => 'images/axialis/hardware-network/power-plug.svg',
            'C__OBJTYPE__PERSON'               => 'images/axialis/user-management/user.svg',
            'C__OBJTYPE__PERSON_GROUP'         => 'images/axialis/user-management/user-group.svg',
            'C__OBJTYPE__PHONE'                => 'images/axialis/hardware-network/phone-6.svg',
            'C__OBJTYPE__PRINTBOX'             => 'images/axialis/hardware-network/printer-network.svg',
            'C__OBJTYPE__PRINTER'              => 'images/axialis/hardware-network/printer.svg',
            'C__OBJTYPE__RACK_SEGMENT'         => 'images/axialis/hardware-network/blade-chassis.svg',
            'C__OBJTYPE__RELATION'             => 'images/axialis/user-interface/fullscreen.svg',
            'C__OBJTYPE__REPLICATION'          => 'images/axialis/development/addin-office.svg',
            'C__OBJTYPE__RM_CONTROLLER'        => 'images/axialis/hardware-network/hardware-1.svg',
            'C__OBJTYPE__ROOM'                 => 'images/axialis/cad/technical-plan.svg',
            'C__OBJTYPE__ROUTER'               => 'images/axialis/hardware-network/router-2.svg',
            'C__OBJTYPE__SAN'                  => 'images/axialis/hardware-network/storage.svg',
            'C__OBJTYPE__SAN_ZONING'           => 'images/axialis/cad/layers.svg',
            'C__OBJTYPE__SERVER'               => 'images/axialis/hardware-network/server-single.svg',
            'C__OBJTYPE__SERVICE'              => 'images/axialis/development/application-console-filled.svg',
            'C__OBJTYPE__SIM_CARD'             => 'images/axialis/hardware-network/sim-card.svg',
            'C__OBJTYPE__SOA_STACK'            => 'images/axialis/spreadsheet/formating-data-bars-blue.svg',
            'C__OBJTYPE__STACKING'             => 'images/axialis/hardware-network/storage.svg',
            'C__OBJTYPE__SUPERNET'             => 'images/axialis/web-email/internet-network-green.svg',
            'C__OBJTYPE__SWITCH'               => 'images/axialis/hardware-network/storage-network.svg',
            'C__OBJTYPE__SWITCH_CHASSIS'       => 'images/axialis/hardware-network/blade-chassis.svg',
            'C__OBJTYPE__TELEPHONE_SYSTEM'     => 'images/axialis/hardware-network/phone-system.svg',
            'C__OBJTYPE__UPS'                  => 'images/axialis/development/event-filled.svg',
            'C__OBJTYPE__VEHICLE'              => 'images/axialis/transportation/vehicle-car-side.svg',
            'C__OBJTYPE__VIRTUAL_CLIENT'       => 'images/axialis/hardware-network/workplace.svg',
            'C__OBJTYPE__VIRTUAL_HOST'         => 'images/axialis/hardware-network/drive.svg',
            'C__OBJTYPE__VIRTUAL_SERVER'       => 'images/axialis/hardware-network/server-virtual.svg',
            'C__OBJTYPE__VOIP_PHONE'           => 'images/axialis/hardware-network/phone-5.svg',
            'C__OBJTYPE__VRRP'                 => 'images/axialis/hardware-network/power-plug.svg',
            'C__OBJTYPE__WAN'                  => 'images/axialis/web-email/cloud.svg',
            'C__OBJTYPE__WIRING_SYSTEM'        => 'images/axialis/development/event.svg',
            'C__OBJTYPE__WORKSTATION'          => 'images/axialis/hardware-network/workplace.svg',
        ];

        foreach ($mapping as $objectTypeConstant => $newIconPath) {
            try {
                $readSql = "SELECT isys_obj_type__title as title, isys_obj_type__icon as oldIconPath
                    FROM isys_obj_type
                    WHERE isys_obj_type__const = '{$objectTypeConstant}'
                    LIMIT 1;";

                $objectTypeData = $dao->retrieve($readSql)->get_row();

                if ($objectTypeData === null) {
                    echo "It seems like object type <code>{$objectTypeConstant}</code> does not exist in your installation, skipping...<br />";
                    continue;
                }

                $updateSql = "UPDATE isys_obj_type
                    SET isys_obj_type__icon = '{$newIconPath}'
                    WHERE isys_obj_type__const = '{$objectTypeConstant}'
                    LIMIT 1;";

                $dao->update($updateSql);

                if (!$dao->affected_after_update()) {
                    echo "Object type <strong>'{$language->get($objectTypeData['title'])}'</strong> was already up-to-date!<br />";
                    continue;
                }

                $dao->apply_update();

                echo "Updated icon of <strong>'{$language->get($objectTypeData['title'])}'</strong> ({$objectTypeConstant}), from <code>'{$objectTypeData['oldIconPath']}'</code> to <code>'{$newIconPath}'</code><br />";
            } catch (Exception $e) {
                echo "Error during update for {$objectTypeConstant}: {$e->getMessage()}<br />";
            }
        }
    }
}
