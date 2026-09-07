<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Category\G\Ip;

use idoit\Component\Helper\Ip;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_application;
use isys_smarty_plugin_f_text;

/**
 * Class Ipv4AddressFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class Ipv4AddressFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__UI__TYPE__TEXT;

    /**
     * @var bool
     */
    public static $changeAll = true;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $type = self::$type;
        $viewValue = ($valueFormatter->getValue() ?: (new Value()))->getViewValue();
        $sortValue = Ip::ip2long($viewValue);
        $oldValue = Ip::validate_ip($viewValue) ? $viewValue : '';

        $content = "<td data-cell-type='{$type}' data-old-value='{$oldValue}' data-sort='{$sortValue}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";
        $pluginContent = '';

        try {
            $language = isys_application::instance()->container->get('language');
            $template = isys_application::instance()->container->get('template');

            $params = $valueFormatter->getProperty()->getUi()->getParams();

            $params['name'] = null;
            $params['p_strClass'] .= " input-small {$valueFormatter->getPropertyKey()} ";
            $params['inputGroupMarginClass'] = '';
            $params['p_strValue'] = $viewValue;

            if ($valueFormatter->getPropertyKey() && !$valueFormatter->isDeactivated()) {
                $params['name'] = "{$valueFormatter->getPropertyKey()}[{$valueFormatter->getObjectId()}-{$valueFormatter->getEntryId()}]";
            }

            $params['p_onChange'] .= ";window.multiEdit.changed(null, '{$params['name']}')";

            if ($valueFormatter->isDisabled()) {
                unset($params['p_strValue']);
                $params['p_bDisabled'] = true;
                $params['p_strClass'] .= ' multiedit-disabled ';
            }

            if ($valueFormatter->isChangeAllRowsActive() && self::$changeAll && $params['name'] !== null) {
                $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'text');";
                unset($params['p_bDisabled']);
            }

            $pluginContent = (new isys_smarty_plugin_f_text())->navigation_edit($template, $params);
        } catch (\Exception $e) {
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled. Message: " . $e->getMessage());
        }

        // Can not use sprintf because there is a problem with Strings which have '%' in it. See category property 'service_level'
        return str_replace('%s', $pluginContent, $content);
    }
}
