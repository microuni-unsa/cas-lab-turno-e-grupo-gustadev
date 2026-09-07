<?php
/**
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use idoit\Component\Helper\Purify;
use idoit\Module\License\Event\License\LegacyLicenseRemovedEvent;
use idoit\Module\License\Exception\LicenseExistsException;
use idoit\Module\License\Exception\LicenseInvalidException;
use idoit\Module\License\Exception\LicenseParseException;
use idoit\Module\License\Exception\LicenseServerAuthenticationException;
use idoit\Module\License\Exception\LicenseServerConnectionException;
use idoit\Module\License\Exception\LicenseServerNoLicensesException;
use idoit\Module\License\LicenseService;

global $g_comp_database, $g_config, $g_absdir, $g_license_token;

$l_template = isys_component_template::instance();

if (!C__ENABLE__LICENCE) {
    throw new Exception("License pages are not available in this i-doit version! " . "You need to subscribe at <a href=\"https://www.i-doit.com\">https://www.i-doit.com</a>.");
}

/* Load statistics module */
include_once($g_absdir . '/src/classes/modules/statistics/init.php');

$app = isys_application::instance();

$l_licences = new isys_module_licence();
$l_dao_mandator = new isys_component_dao_mandator($app->database_system);

$licenses = [];
$l_licences_single = [];
$l_licences_hosting = [];
$flashes = $_SESSION['flash'] ?? [];

$_SESSION['flash'] = [];

function addFlash(string $type, string $message, bool $reload = true): void
{
    $_SESSION['flash'][] = [
        'type'    => $type,
        'message' => $message
    ];

    if ($reload) {
        header('Location: ?req=licences');
        die;
    }
}

global $licenseService;

/* Request processing */
switch ($_POST["action"] ?? '') {
    case "delete":

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $l_licence_data) {
                [$l_tenant_id, $l_licence_id] = explode(",", $l_licence_data);

                // License type is not set in the ID (was assumed as third array item in 'list(...)').
                $licenceType = '';

                if ($l_licence_id > 0 && $l_tenant_id >= 0) {
                    if ($licenceType === 'hosting' && (int)$_POST['multiLicenceAction']) {
                        // Delete all installed child licenses referenced by the parent license
                        $l_licences->deleteLicenceByParentLicence($app->database_system, $l_licence_id);
                    }

                    //connect_mandator($l_tenant_id);
                    $l_licences->delete_licence($app->database_system, $l_licence_id);

                    if ($l_tenant_id === 0 && $l_licence_id > 0) {
                        $app->database_system->query("DELETE FROM isys_licence WHERE isys_licence__type = " . C__LICENCE_TYPE__HOSTING_SINGLE);
                    } else {
                        $app->database_system->query("DELETE FROM isys_licence WHERE isys_licence__id = " . (int)$l_licence_id . ";");
                    }

                    $licenseService->getEventDispatcher()->dispatch(
                        new LegacyLicenseRemovedEvent(),
                        LegacyLicenseRemovedEvent::NAME
                    );
                }
            }

            addFlash('box-green', 'License was deleted');
        }

        break;
    case "web_license_set_token": {
        if (empty($_POST['license_token'])) {
            $l_template->assign('error', 'License Token cannot be empty');
            break;
        }

        try {
            if (Purify::purifyValueByRegex($_POST['license_token'], '/[^[:alnum:]]+/') !== $_POST['license_token']) {
                throw new Exception('Invalid license token');
            }
            saveLicenseToken($_POST['license_token']);

            addFlash('box-green', 'License successfully installed. Please remember to clear your browser cache and logout from i-doit once to see the changes. (This needs to be done by every user)');
        } catch (Exception $e) {
            $l_template->assign('error', $e->getMessage());
        }

        break;
    }
    case "web_license_save_token":
        try {
            if (Purify::purifyValueByRegex($_POST['license_token'], '/[^[:alnum:]]+/') !== $_POST['license_token']) {
                throw new Exception('Invalid license token');
            }
            $licenseService->setEncryptionToken($_POST['license_token']);

            try {
                $webLicensesString = $licenseService->getLicensesFromServer();
            } catch (GuzzleException $exception) {
                $l_template->assign('error', $exception->getMessage());
                break;
            } catch (LicenseServerAuthenticationException $exception) {
                $l_template->assign('error', $exception->getMessage());
                break;
            } catch (LicenseServerConnectionException $exception) {
                $l_template->assign('error', $exception->getMessage());
                break;
            } catch (LicenseServerNoLicensesException $exception) {
                $l_template->assign('note', $exception->getMessage());
            }

            saveLicenseToken($_POST['license_token']);

            try {
                foreach ($licenseService->parseEncryptedLicenses($webLicensesString) as $license) {
                    $licenseService->installLicense($license);
                }
            } catch (LicenseExistsException $exception) {
                $l_template->assign('note', 'Some licenses were already existing');
            } catch (LicenseInvalidException $exception) {
                $l_template->assign('note', 'Some licenses were skipped because they were invalid');
            } catch (LicenseParseException $exception) {
                $l_template->assign('error', 'Given licenses could not be installed because string is malformed');
            } catch (\Exception $exception) {
                $l_template->assign('error', 'An error occured');
            }
        } catch (Exception $e) {
            $l_template->assign('error', $e->getMessage());
        }
        break;
    case "web_license_check_licenses":
        try {
            $webLicensesString = $licenseService->getLicensesFromServer();
        } catch (GuzzleException $exception) {
            $l_template->assign('error', $exception->getMessage());
            break;
        } catch (LicenseServerAuthenticationException $exception) {
            $l_template->assign('error', $exception->getMessage());
            break;
        } catch (LicenseServerConnectionException $exception) {
            $l_template->assign('error', $exception->getMessage());
            break;
        } catch (LicenseServerNoLicensesException $exception) {
            $l_template->assign('note', $exception->getMessage());
        }

        try {
            foreach ($licenseService->parseEncryptedLicenses($webLicensesString) as $license) {
                try {
                    $licenseService->installLicense($license);
                } catch (LicenseExistsException $exception) {
                    $l_template->assign('note', 'Some licenses were already existing');
                } catch (LicenseInvalidException $exception) {
                    $l_template->assign('note', 'Some licenses were skipped because they were expired');
                } catch (\Exception $exception) {
                    $l_template->assign('error', 'An error occured');
                }
            }
        } catch (LicenseParseException $exception) {
            $l_template->assign('error', 'Given licenses could not be installed because string is malformed');
        }
        break;
    case "web_license_remove_token":
        saveLicenseToken('');
        $licenseService->deleteLicenses([LicenseService::C__LICENCE_TYPE__NEW__IDOIT, LicenseService::C__LICENCE_TYPE__NEW__ADDON]);

        addFlash('box-green', 'Token was removed');
        break;

    case "add":
        $mandatorDatabase = null;

        if (!empty($_POST['license_file_raw'])) {
            // Handle new licenses
            try {
                $licenses = 0;
                $errors = 0;
                foreach ($licenseService->parseEncryptedLicenses($_POST['license_file_raw']) as $license) {
                    try {
                        $licenses++;
                        $licenseService->installLicense($license);
                    } catch (LicenseExistsException $exception) {
                        // Do nothing, this is not an error.
                    } catch (LicenseInvalidException $exception) {
                        // Do nothing, but take note of the error.
                        $errors ++;
                    }
                }

                // @see ID-11562 After all licenses where created without an error, we can clear the server communication log.
                if ($errors === 0) {
                    $licenseService->clearLicenseServerCommunicationLog();
                    addFlash('box-green', 'License successfully installed. Please remember to clear your browser cache and logout from i-doit once to see the changes. (This needs to be done by every user)');
                }

                addFlash('box-yellow', "{$errors} licenses could not be installed successfully. If this message keeps showing up or any license is missing, please consult our support.", false);
                addFlash('box-green', "{$licenses} licenses installed successfully. Please remember to clear your browser cache and logout from i-doit once to see the changes. (This needs to be done by every user)");
            } catch (LicenseParseException $exception) {
                $l_template->assign('error', 'Given licenses could not be installed because string is malformed');
            } catch (\Exception $exception) {
                $l_template->assign('error', 'An error occured');
            }
        } else {
            isys_module_system::handle_licence_installation($l_tenant, $mandatorDatabase);
        }

        $l_frontend_error = $l_template->get_template_vars('error');

        // Only redirect, if there is no error message!
        if (empty($l_frontend_error)) {
            addFlash('box-green', 'License successfully installed. Please remember to clear your browser cache and logout from i-doit once to see the changes. (This needs to be done by every user)');
        }
}

try {
    if ($app->database_system) {
        $totalObjects = 0;

        // New licenses
        $licenseEntities = $licenseService->getLicenses();

        foreach ($licenseEntities as $id => $licenseEntity) {
            $start = \Carbon\Carbon::createFromTimestamp($licenseEntity->getValidityFrom()->getTimestamp());
            $end = \Carbon\Carbon::createFromTimestamp($licenseEntity->getValidityTo()->getTimestamp());

            $invalid = !(\Carbon\Carbon::now()->between($start, $end));

            $start = $start->format('l, F j, Y');
            $end = $end->format('l, F j, Y');

            $licenses[$licenseEntity->getProductType()][$id] = [
                'label' => $licenseEntity->getProductName() ?: $licenseEntity->getProductIdentifier(),
                'licenseType' => $licenseEntity->getProductType(),
                'start' =>  $start,
                'end' => $licenseEntity->isUnlimited() ? 'Your license has no expiration date!' : $end, // @see ID-8999
                'objects' => $licenseEntity->getObjects(),
                'tenants' => $licenseEntity->getTenants(),
                'environment' => $licenseEntity->getEnvironment(),
                'daysLeft' => $licenseEntity->daysLeft(),
                'isEval' => $licenseEntity->isIdoitLicense() && $licenseEntity->isEvaluation(),
            ];
        }

        $oldLicenses = $licenseService->getLegacyLicenses();

        foreach ($oldLicenses as $oldLicense) {
            $licenseUnlimited = false;
            $start = Carbon::createFromTimestamp($oldLicense[LicenseService::C__LICENCE__REG_DATE]);
            $end = Carbon::parse($oldLicense[LicenseService::LEGACY_LICENSE_EXPIRES]);

            $invalid = !(\Carbon\Carbon::now()->between($start, $end));

            // @see ID-8999 Find out if a license is unlimited.
            $licenseUnlimited = in_array(
                $oldLicense[LicenseService::LEGACY_LICENSE_TYPE],
                [LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE, LicenseService::C__LICENCE_TYPE__BUYERS_LICENCE_HOSTING]
            );

            $start = $start->format('l, F j, Y');
            $endFormated = $end->format('l, F j, Y');
            $daysLeft = $end->diffInDays(Carbon::now()) + 1;

            $label = 'Subscription (Classic)';
            $tenants = 1;

            if (in_array(
                $oldLicense[LicenseService::LEGACY_LICENSE_TYPE],
                LicenseService::LEGACY_LICENSE_TYPES_HOSTING,
                false
            )) {
                $label = 'Hosting (Classic)';
                $tenants = 50;
            }

            if ($oldLicense[LicenseService::C__LICENCE__IS_EVALUATION]) {
                $label = 'i-doit (30-day-trial)';
            }

            $licenses['idoit'][$oldLicense[LicenseService::LEGACY_LICENSE_ID]] = [
                'label' => $label,
                'start' =>  $start,
                'end' => $licenseUnlimited ? 'Your license has no expiration date!' : $endFormated,
                'daysLeft' => $daysLeft,
                'objects' => $oldLicense[LicenseService::C__LICENCE__OBJECT_COUNT],
                'tenants' => $tenants,
                'environment' => 'production',
                'invalid' => $invalid,
                'isEval' => $oldLicense[LicenseService::C__LICENCE__IS_EVALUATION]
            ];
        }
    }
} catch (isys_exception_database $e) {
    $l_template->assign("error", $e->getMessage());
}

$lastCommunicationLog = $licenseService->getLastLicenseServerCommuncation();

$l_template
    ->assign("licences", $licenses)
    ->assign("licensedAddOns", $licenseService->getLicensedAddOns())
    ->assign("totalLicenseObjects", $licenseService->getTotalObjects())
    ->assign("licenseObjectsUsed", $licenseService->getUsedLicenseObjects(true))
    ->assign("totalTenants", $licenseService->getTotalTenants())
    ->assign("licenseToken", $g_license_token)
    ->assign("flashes", $flashes)
    ->assign("lastCommunicationLog", ($lastCommunicationLog !== null
        ? sprintf(
            'Last check on %s: %s, %d licenses retrieved',
            \Carbon\Carbon::parse($lastCommunicationLog['created'])->format('l, F j, Y H:i'),
            in_array($lastCommunicationLog['status'], LicenseService::HTTP_STATUS_POSITIVE) ? 'OK' : 'ERROR',
            $lastCommunicationLog['licenses_count']
        )
        : ''));
