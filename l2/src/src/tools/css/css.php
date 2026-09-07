<?php
/**
 * i-doit
 *
 * Call stylesheet data through cache/smarty.
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

header("Content-Type: text/css");

// Enabling a cache lifetime of one week.
isys_core::expire(isys_convert::WEEK);

$appPath = rtrim(isys_application::instance()->app_path, '/') . '/';

$template = isys_application::instance()->container->get('template');
$signals = isys_application::instance()->container->get('signals');
$session = isys_application::instance()->container->get('session');
$userSettings = isys_application::instance()->container->get('settingsUser');

// @see ID-8965 Prepare a 'per user' CSS cache.
$compiledCssPath = $appPath . 'temp/compiled-style_' . $session->get_mandator_id() . '_' . $session->get_user_id() . '.css';

$paddingCss = '';
$spacerCss = '';
$treeCss = '';

// @see ID-8965 Allow custom paddings for category rows.
switch ($userSettings->get('gui.category.padding', 'l')) {
    case 'm':
        $paddingCss = '/* Customized row padding */' . PHP_EOL .
            'table.contentTable td { padding: 5px 0; height: 42px; }' . PHP_EOL .
            'table.contentTable td.key.vat { padding-top: 14px; }' . PHP_EOL;
        break;

    case 's':
        $paddingCss = '/* Customized row padding */' . PHP_EOL .
            'table.contentTable td { padding: 2px 0; height: 36px; }' . PHP_EOL .
            'table.contentTable td.key.vat { padding-top: 12px; }' . PHP_EOL;
        break;
}

if ($userSettings->get('gui.category.spacer', 1)) {
    $spacerCss = '/* Spacers */' . PHP_EOL .
        '.category-spacer { height: auto !important; }' . PHP_EOL;
} else {
    $spacerCss = '/* Spacers */' . PHP_EOL .
        '.category-spacer { display: none; }' . PHP_EOL;
}

// @see ID-9164 Update CSS-Tree spacings
switch ($userSettings->get('gui.tree.spacing', 'l')) {
    case 'm':
        $treeCss = '/* Trees */' . PHP_EOL .
            'ul.css-tree { margin-left: 17px; line-height: 22px; }' . PHP_EOL .
            'ul.css-tree .css-tree { margin-top: 0; margin-bottom: 0; }' . PHP_EOL .
            'ul.css-tree li::before { width: 13px; top: 2px; }' . PHP_EOL .
            'ul.css-tree li .child-toggle { top: 8px; }' . PHP_EOL;
        break;

    case 's':
    case 'compact':
        $treeCss = '/* Trees */' . PHP_EOL .
            'ul.css-tree { margin-left: 14px; line-height: 18px; }' . PHP_EOL .
            'ul.css-tree .css-tree { margin-top: 0; margin-bottom: 0; }' . PHP_EOL .
            'ul.css-tree li::before { width: 12px; top: 1px; }' . PHP_EOL .
            'ul.css-tree li .child-toggle { top: 8px; }' . PHP_EOL;
        break;
}

if (file_exists($compiledCssPath)) {
    echo file_get_contents($compiledCssPath) . $paddingCss . $spacerCss . $treeCss;
    die;
}

// Read every file from this directory.
$cssDirectory = $appPath . 'src/themes/default/css';

// Set CSS variables to use.
$template->assign("dir_images", rtrim(isys_application::instance()->www_path, '/') . '/images/');

$signals->emit('mod.css.beforeProcess');

// @see ID-10010 Remove outdated output filter

try {
    if (!is_dir($cssDirectory)) {
        throw new isys_exception_filesystem('"' . $cssDirectory . '" is not a directory!', 'The given directory "' . $cssDirectory . '" is no directory or does not exist.');
    }

    if (!file_exists($cssDirectory . '/style.css') || !is_readable($cssDirectory . '/style.css')) {
        throw new isys_exception_filesystem('The main "style.css" file does not exist or is not readable - please go sure that the latest i-doit update ran without errors!');
    }

    $combinedCss = $template->fetch($cssDirectory . '/style.css') . PHP_EOL . PHP_EOL;

    foreach (glob($cssDirectory . '/*.css') as $file) {
        // Skip print and 'base' styles.
        if (in_array(basename($file), ['style.css', 'print.css'], true)) {
            continue;
        }

        $combinedCss .= '/* Contents of ' . basename($file) . ' */' . PHP_EOL . $template->fetch($file) . PHP_EOL . PHP_EOL;
    }
} catch (isys_exception $exception) {
    die("Error while creating CSS: " . $exception->getMessage());
}

$signals->emit('mod.css.processed', $combinedCss);

echo $combinedCss . $paddingCss . $spacerCss . $treeCss;

if (isys_settings::get('css.caching.cache-to-temp', true)) {
    isys_file_put_contents($compiledCssPath, $combinedCss);
}

die;
