<?php
/**
 * i-doit environment info collector
 *
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @copyright synetics GmbH
 */
global $g_db_system, $g_absdir;

if (!$g_db_system && !defined("C__ADMIN_CENTER")) {
    die("This file is part of the admin center and cannot be run standalone.");
}

session_start();

$link = new mysqli($g_db_system["host"], $g_db_system["user"], $g_db_system["pass"], $g_db_system["name"], $g_db_system["port"]);
$result = $link->query("SELECT * FROM isys_db_init");

$systemInfo = [];
while ($row = $result->fetch_assoc()) {
    $systemInfo[$row["isys_db_init__key"]] = $row["isys_db_init__value"];
}

// See composer.json for mandatory extensions.
$mandatoryExtensions = ['curl', 'gd', 'json', 'ldap', 'libxml', 'mbstring', 'mysqli', 'pcre', 'pdo', 'phar', 'session', 'simplexml', 'sockets', 'spl', 'xml', 'zip', 'zlib'];

// See package.json files from each add-on.
$optionalExtensions = [];

foreach (glob("{$g_absdir}src/classes/modules/*/package.json") as $packageJsonFile) {
    $packageJsonData = json_decode(file_get_contents($packageJsonFile), true);

    if (!isset($packageJsonData['dependencies']['php']) || !is_array($packageJsonData['dependencies']['php'])) {
        continue;
    }

    $optionalExtensions = array_merge($optionalExtensions, $packageJsonData['dependencies']['php']);
}

// Fetch all optional extensions that are not part of our required extensions.
$optionalExtensions = array_diff(array_filter(array_unique($optionalExtensions)), $mandatoryExtensions);

$installedExtensions = get_loaded_extensions();

// Calculate missing required and optional extensions.
$missingMandatoryExtenions = array_diff($mandatoryExtensions, array_map('strtolower', $installedExtensions));
$missingOptionalExtenions = array_diff($optionalExtensions, array_map('strtolower', $installedExtensions));

$l_out = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" .
    "<environment>\n" .
    "  <title>i-doit environment info</title>\n" .
    "  <product>" . $systemInfo["title"] . " Revision " . $systemInfo["revision"] . "</product>\n" .
    "  <useragent>" . $_SERVER['HTTP_USER_AGENT'] . "</useragent>\n" .
    "  <serveros>" . php_uname() . "</serveros>\n" .
    "  <webserver>" . $_SERVER['SERVER_SOFTWARE'] . "</webserver>\n" .
    "  <mysql>" . mysqli_get_server_info($link) . "</mysql>\n" .
    "  <php>" . phpversion() . "</php>\n" .
    "  <phpExtensions>\n";

foreach ($installedExtensions as $extension) {
    $l_out .= "    <phpextension name=\"{$extension}\" version=\"" . phpversion($extension) . "\">Installed</phpextension>\n";
}

$l_out .= "  </phpExtensions>\n" .
    "  <missingPhpExtensions>\n" .
    "    <mandatory>\n";

foreach ($missingMandatoryExtenions as $extension) {
    $l_out .= "      <phpextension name=\"{$extension}\">Missing</phpextension>\n";
}

$l_out .= "    </mandatory>\n" .
    "    <optional>\n";

foreach ($missingOptionalExtenions as $extension) {
    $l_out .= "      <phpextension name=\"{$extension}\">Missing</phpextension>\n";
}

$l_out .= "    </optional>\n" .
    "  </missingPhpExtensions>\n";

ob_start();
phpinfo(5);

$phpinfo = ['phpinfo' => []];

preg_match_all(
    '#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s',
    ob_get_clean(),
    $matches,
    PREG_SET_ORDER
);

if (is_array($matches) && count($matches)) {
    foreach ($matches as $match) {
        $infoKeys = array_keys($phpinfo);

        if (strlen($match[1])) {
            $phpinfo[$match[1]] = [];
        } elseif (isset($match[3])) {
            $phpinfo[end($infoKeys)][$match[2]] = isset($match[4]) ? [
                $match[3],
                $match[4]
            ] : $match[3];
        } else {
            $phpinfo[end($infoKeys)][] = $match[2];
        }
    }
}

function cleanXmlAttribute(string $str): string
{
    return implode('', array_map('ucfirst', explode(' ', preg_replace('~[^a-zA-Z]~', ' ', $str))));
}

function cleanXmlValue(string $str): string
{
    return str_replace('&', '&amp;', html_entity_decode(strip_tags($str)));
}

foreach ($phpinfo as $name => $section) {
    if (is_array($section) && !empty($section)) {
        $name = cleanXmlAttribute($name);
        $l_out .= "    <{$name}>\n";

        foreach ($section as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                continue;
            }

            $key = cleanXmlAttribute($key);

            if (is_array($value)) {
                $l_out .= "      <{$key}>" . cleanXmlValue($value[0]) . ", " . cleanXmlValue($value[1]) . "</{$key}>\n";
            } elseif (is_string($value)) {
                $l_out .= "      <{$key}>" . cleanXmlValue($value) . "</{$key}>\n";
            }
        }

        $l_out .= "    </{$name}>\n";
    }
}

$l_out .= "</environment>\n";

ob_end_clean();

header("Content-Type: text/xml");
header("Content-Disposition: attachment; filename=idoit-environment_" . date("Ymd") . ".xml");
header("Pragma: no-cache");

echo $l_out;
die;
