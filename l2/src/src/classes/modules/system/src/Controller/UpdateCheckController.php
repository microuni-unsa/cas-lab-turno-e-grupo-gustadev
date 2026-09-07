<?php

namespace idoit\Module\System\Controller;

use Exception;
use isys_application;
use isys_settings;
use isys_update;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * System relevant add-on controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class UpdateCheckController
{
    public function check(): Response
    {
        try {
            include_once BASE_DIR . '/updates/classes/isys_update.class.php';

            $update = new isys_update();
            $updateInfo = $update->get_isys_info();
            $nextAvailableVersion = null;
            $updateMessage = '';

            try {
                // @see  ID-6872  System settings can now provide a update-XML URL.
                if (defined('C__IDOIT_UPDATES_PRO') || isys_settings::has('system.update-xml-url.pro')) {
                    $updateXmlUrl = isys_settings::get('system.update-xml-url.pro', C__IDOIT_UPDATES_PRO);
                } else {
                    $updateXmlUrl = isys_settings::get('system.update-xml-url.open', C__IDOIT_UPDATES);
                }

                $xmlContent = isys_application::instance()->container->get('http_client')
                    ->request($updateXmlUrl)
                    ->getBody()
                    ->getContents();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            $availableVersions = $update->get_new_versions($xmlContent);

            if (!is_array($availableVersions) || count($availableVersions) === 0) {
                throw new Exception('Update check failed. Is the i-doit server not connected to the internet?');
            }

            foreach ($availableVersions as $version) {
                if ($updateInfo["revision"] < $version["revision"]) {
                    $nextAvailableVersion = $version;
                    $releaseDate = date('d.m.Y', strtotime($version['release']));
                    $updateMessage = "There is a new i-doit version available!<br /><strong>{$version['title']}</strong> Revision {$version['revision']} (Released {$releaseDate})";
                }
            }

            if ($nextAvailableVersion === null) {
                $updateMessage = 'You have got the latest version.';
            }

            return new JsonResponse([
                'success' => true,
                'data'    => $nextAvailableVersion,
                'message' => $updateMessage
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ]);
        }
    }
}
