<?php
/**
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
global $g_comp_database_system;

use idoit\Component\Helper\Unserialize;
use idoit\Module\Cmdb\Model\Ci\Table\Config;
use idoit\Module\Cmdb\Model\Ci\Table\Property;
use PHPMailer\PHPMailer\PHPMailer;

isys_settings::initialize($g_comp_database_system);

$passwordFields = [];
$definition = isys_settings::get_definition();

foreach ($definition as $topic => $settings) {
    foreach ($settings as $settingKey => $settingDefinition) {

        if ($settingDefinition['type'] === 'password') {
            $passwordFields[] = $settingKey;
        }
    }
}

if (isset($_GET['expert'])) {
    if (isset($_GET['action']) && $_GET['action'] === 'save') {
        $l_result = [
            'success' => true,
            'message' => 'Config successfully overwritten, page will reload.'
        ];

        try {
            $rawSettings = json_decode($_POST['settings'], true);

            $removeSettingsKeys = array_map(function ($key) {
                return substr($key, 8);
            }, array_keys(array_filter($rawSettings, function ($value, $key) {
                return (strpos($key, 'remove__') === 0 && $value == '1');
            }, ARRAY_FILTER_USE_BOTH)));

            $updateSettings = array_filter($rawSettings, function ($key) use ($removeSettingsKeys) {
                return strpos($key, 'setting__') === 0 && !in_array(substr($key, 9), $removeSettingsKeys, true);
            }, ARRAY_FILTER_USE_KEY);

            $createSettings = [];

            if (isset($rawSettings['custom__key[]']) && is_array($rawSettings['custom__key[]'])) {
                foreach ($rawSettings['custom__key[]'] as $index => $key) {
                    if (!isset($rawSettings['custom__value[]'][$index]) || trim($rawSettings['custom__value[]'][$index]) === '' || trim($key) === '') {
                        continue;
                    }

                    $createSettings[$key] = $rawSettings['custom__value[]'][$index];
                }
            } elseif (isset($rawSettings['custom__key[]']) && is_string($rawSettings['custom__key[]']) && is_string($rawSettings['custom__value[]'])) {
                if (trim($rawSettings['custom__key[]']) !== '' && trim($rawSettings['custom__value[]']) !== '') {
                    $createSettings[$rawSettings['custom__key[]']] = $rawSettings['custom__value[]'];
                }
            }

            foreach ($removeSettingsKeys as $key) {
                isys_settings::remove($key);
            }

            foreach ($updateSettings as $key => $value) {
                isys_settings::set(substr($key, 9), $value);
            }

            foreach ($createSettings as $key => $value) {
                isys_settings::set($key, $value);
            }

            isys_settings::force_save();
        } catch (Exception $e) {
            $l_result['success'] = false;
            $l_result['message'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($l_result);
        die;
    }

    // Array with keys that should be hidden.
    $hiddenInternalSetting = [
        'flow.installed'
    ];

    function isInternalSetting($settingKey, $settingValue): bool
    {
        $internalConfigurationKeys = [
            'system.start-of-end',
            'cmdb.objtype-',
            'cmdb.object-browser.C__',
            'cmdb.base-object-list.config.C__',
            'cmdb.base-object-table.config.C__',
        ];

        try {
            if (str_starts_with($settingKey, 'admin.')) {
                return true;
            }

            foreach ($internalConfigurationKeys as $key) {
                if (str_contains($settingKey, $key)) {
                    // If we find a "internal" configuration jump out immediately!
                    return true;
                }
            }

            if (is_scalar($settingValue)) {
                if (!$settingValue) {
                    return false;
                }

                // @see ID-9433 Only allow specific classes to be unserialized.
                // @see ID-10724 Check if the string starts with 'O:' before trying to unserialize it.
                if (str_starts_with($settingValue, 'O:') && !Unserialize::toObject($settingValue, [Config::class, Property::class]) instanceof stdClass) {
                    return true;
                }

                if (isys_format_json::is_json($settingValue) && !is_scalar(isys_format_json::decode($settingValue))) {
                    return true;
                }
            }
        } catch (Exception $e) {
        }

        return false;
    }

    $settings = isys_settings::get();

    foreach ($settings as $l_key => $l_value) {
        if (!isInternalSetting($l_key, $l_value)) {
            $settings[$l_key] = isys_glob_htmlentities($l_value);
        }

        // Currently only used for 'flows'
        // @see ID-12249 Unset password fields so they do not get shown in plaintext.
        if (in_array($l_key, $hiddenInternalSetting) || in_array($l_key, $passwordFields)) {
            unset($settings[$l_key]);
        }
    }

    // Strip html again, append necessary tags when saving
    $settings['system.login.welcome-message'] = filter_var($settings['system.login.welcome-message'], FILTER_SANITIZE_STRING);

    // Necessary 'hack' to include a different template.
    $_GET["req"] = 'settings-expert';

    isys_component_template::instance()
        ->assign('settings', $settings);
} else {
    if (isset($_GET['action']) && $_GET['action'] === 'save') {
        $l_result = [
            'success' => true,
            'message' => 'Config successfully overwritten.'
        ];

        try {
            $settingsData = [];

            // Strip 'setting__' from the beginning:
            foreach (json_decode($_POST['settings'], true) as $key => $value) {
                if (!str_starts_with($key, 'setting__')) {
                    continue;
                }

                $key = substr($key, 9);

                // @see ID-9310 Implement specific logic to save 'chosen' data for the default tenant.
                if ($key === 'session.sso.mandator-id' && is_array($value)) {
                    $value = implode(',', $value);
                }

                $settingsData[$key] = $value;
            }

            // @see ID-12249 Remove 'unchanged' passwords before saving.
            foreach ($passwordFields as $passwordField) {
                if (isset($settingsData[$passwordField . '__action']) && $settingsData[$passwordField . '__action'] === 'unchanged') {
                    unset($settingsData[$passwordField]);
                }

                // Either way, remove the 'action' field.
                unset($settingsData[$passwordField . '__action']);
            }

            foreach ($settingsData as $key => $value) {
                isys_settings::set($key, $value);
            }

            isys_settings::force_save();
        } catch (Exception $e) {
            $l_result['success'] = false;
            $l_result['message'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($l_result);
        die;
    }

    if (!empty($_GET['action']) && $_GET['action'] === 'checkSMTP' && !empty($_POST)) {
        // @see ID-12249 Use saved data so we do not need to show the password in the UI.
        $configuration = [
            'host'          => isys_settings::get('system.email.smtp-host'),
            'port'          => isys_settings::get('system.email.port'),
            'timeout'       => isys_settings::get('system.email.connection-timeout'),
            'username'      => isys_settings::get('system.email.username'),
            'password'      => isys_settings::get('system.email.password'),
            'smtp-auto-tls' => isys_settings::get('system.email.smtp-auto-tls'),
        ];

        if (checkSMTPConnection($configuration)) {
            $result = [
                'success' => true,
                'message' => 'SMTP connection established successfully.',
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'Failed to connect to SMTP server. Please save your changes, before testing the connection.',
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($result);
        die;
    }

    $language = isys_application::instance()->container->get('language');

    $sortedDefinition = [];
    $definition = isys_settings::get_definition();

    foreach ($definition as $topic => $settings) {
        $settings = array_filter($settings, fn ($setting) => !(isset($setting['hidden']) && $setting['hidden']));

        if (count($settings)) {
            $sortedDefinition[$language->get($topic)] = $settings;
        }
    }

    $tenantResult = isys_component_dao_mandator::instance($g_comp_database_system)->get_mandator();

    while ($row = $tenantResult->get_row()) {
        $sortedDefinition['Single Sign On']['session.sso.mandator-id']['options'][$row['isys_mandator__id']] = $row['isys_mandator__title'];
    }

    ksort($sortedDefinition);

    $settings = isys_settings::get();
    $settings['session.sso.mandator-id'] = (array)explode(',', $settings['session.sso.mandator-id']);

    // @see ID-12249 Only show '****' instead of the real password.
    foreach ($passwordFields as $passwordField) {
        if (!empty($settings[$passwordField])) {
            $settings[$passwordField] = str_repeat('*', mb_strlen($settings[$passwordField]));
        }
    }

    isys_component_template::instance()
        ->assign('definition', $sortedDefinition)
        ->assign('settings', $settings);
}

/**
 * @param array $settings
 *
 * @return bool
 */
function checkSMTPConnection(array $settings): bool
{
    if (empty($settings['host']) || empty($settings['port'])) {
        return false;
    }

    $mailer = new PHPMailer(true);
    $mailer->Host = $settings['host'];
    $mailer->Port = $settings['port'];
    $mailer->Username = $settings['username'] ?? '';
    $mailer->Password = $settings['password'] ?? '';
    $mailer->Timeout = $settings['timeout'] ?? 30;
    $mailer->SMTPAutoTLS = $settings['smtp-auto-tls'] ? true : false;
    $mailer->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];
    if ($mailer->Username != '' && $mailer->Password != '') {
        $mailer->SMTPAuth = true;
    }
    try {
        $result = $mailer->smtpConnect();
        if ($result) {
            $mailer->smtpClose();

            return true;
        }
    } catch (Exception $e) {
        // should we log this?
    }

    return false;
}
