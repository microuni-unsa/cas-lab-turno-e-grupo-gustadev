<?php

namespace idoit\Module\System\Cleanup;

use isys_cmdb_dao;
use isys_component_dao_result;
use isys_component_template_language_manager;
use isys_helper_color;
use Throwable;

/**
 * Class ReformatColorValues
 *
 * @package idoit\Module\System\Cleanup
 * @see ID-10820
 */
class ReformatColorValues extends AbstractCleanup
{
    private int $changes;
    private isys_cmdb_dao $dao;
    private isys_component_template_language_manager $language;

    /**
     * Method for starting the cleanup process.
     *
     * @throws \Exception
     */
    public function process()
    {
        \isys_module_system::getAuth()->check(\isys_auth::SUPERVISOR, 'SYSTEMTOOLS/CACHE');

        $this->changes = 0;
        $this->dao = $this->container->get('cmdb_dao');
        $this->language = $this->container->get('language');

        echo 'Clean up HEX color values...<br />';

        try {
            $this->cleanupCmdbStatus();
        } catch (Throwable $e) {
            echo 'An error occured while reformatting CMDB status colors:' . $e->getMessage() . '<br />';
            $this->logger->error('An error occured while reformatting CMDB status colors: ' . $e->getMessage());
        }

        try {
            $this->cleanupObjectTypes();
        } catch (Throwable $e) {
            echo 'An error occured while reformatting object type colors:' . $e->getMessage() . '<br />';
            $this->logger->error('An error occured while reformatting object type colors: ' . $e->getMessage());
        }

        try {
            $this->cleanupNetZones();
        } catch (Throwable $e) {
            echo 'An error occured while reformatting net zone colors:' . $e->getMessage() . '<br />';
            $this->logger->error('An error occured while reformatting net zone colors: ' . $e->getMessage());
        }

        if ($this->changes > 0) {
            echo "Done reformatting {$this->changes} HEX color values.<br />";
        } else {
            echo 'No colors needed to be reformatted.<br />';
        }
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    private function cleanupCmdbStatus(): void
    {
        $query = "SELECT isys_cmdb_status__id AS id, isys_cmdb_status__title AS title, isys_cmdb_status__color AS color
            FROM isys_cmdb_status
            WHERE isys_cmdb_status__color NOT REGEXP '^#[0-9a-f]{6}$';";

        $result = $this->dao->retrieve($query);

        if (count($result) === 0) {
            echo 'Colors of <strong>CMDB status</strong> are fine<br />';
            return;
        }

        $updateQuery = 'UPDATE isys_cmdb_status
            SET isys_cmdb_status__color = :newColor
            WHERE isys_cmdb_status__id = :id
            LIMIT 1;';

        echo 'Reformat <strong>CMDB status</strong><br />';
        $this->processCleanUp($result, $updateQuery);
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    private function cleanupObjectTypes(): void
    {
        $query = "SELECT isys_obj_type__id AS id, isys_obj_type__title AS title, isys_obj_type__color AS color
            FROM isys_obj_type
            WHERE isys_obj_type__color NOT REGEXP '^#[0-9a-f]{6}$';";

        $result = $this->dao->retrieve($query);

        if (count($result) === 0) {
            echo 'Colors of <strong>object types</strong> are fine<br />';
            return;
        }

        $updateQuery = 'UPDATE isys_obj_type
            SET isys_obj_type__color = :newColor
            WHERE isys_obj_type__id = :id
            LIMIT 1;';

        echo 'Reformat <strong>object types</strong><br />';
        $this->processCleanUp($result, $updateQuery);
    }

    /**
     * @return void
     * @throws \isys_exception_database
     */
    private function cleanupNetZones(): void
    {
        $query = "SELECT isys_catg_net_zone_options_list__id AS id, isys_obj__title AS title, isys_catg_net_zone_options_list__color AS color
            FROM isys_catg_net_zone_options_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_net_zone_options_list__isys_obj__id
            WHERE isys_catg_net_zone_options_list__color NOT REGEXP '^#[0-9a-f]{6}$';";

        $result = $this->dao->retrieve($query);

        if (count($result) === 0) {
            echo 'Colors of <strong>net zones</strong> are fine<br />';
            return;
        }

        $updateQuery = 'UPDATE isys_catg_net_zone_options_list
            SET isys_catg_net_zone_options_list__color = :newColor
            WHERE isys_catg_net_zone_options_list__id = :id
            LIMIT 1;';

        echo 'Reformat <strong>net zones</strong><br />';
        $this->processCleanUp($result, $updateQuery);
    }

    private function processCleanUp(isys_component_dao_result $result, string $updateQuery): void
    {
        echo '<ul>';

        while ($row = $result->get_row()) {
            $id = (int)$row['id'];

            $title = htmlentities($this->language->get($row['title']));
            $from = htmlentities($row['color']);
            $to = isys_helper_color::unifyHexColor($row['color']);

            // Update the value.
            $this->dao->update(strtr($updateQuery, [':id' => $id, ':newColor' => $this->dao->convert_sql_text($to)]));
            $this->dao->apply_update();

            // Notify the user.
            echo "<li>'{$title}' (#{$row['id']}) changed from '{$from}' to '{$to}' <div class=\"cmdb-marker mb-0\" style=\"background-color: {$to}; float: none; display: inline-block;\"></div></li>";
            $this->changes ++;
        }

        echo '</ul>';
    }
}
