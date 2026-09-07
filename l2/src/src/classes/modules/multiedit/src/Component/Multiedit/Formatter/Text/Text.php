<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Text;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_smarty_plugin_f_text;

/**
 * Class Text
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Text
 */
class Text extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__INFO__TYPE__TEXT;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        $value = ($valueFormatter->getValue() ?: new Value());

        $objectId = $valueFormatter->getObjectId();
        $entryId = $valueFormatter->getEntryId();

        $params = $property->getUi()
            ->getParams();
        $reference = $property->getData()
            ->getReferences();
        $params['name'] = null;
        $id = $valueFormatter->getPropertyKey();

        if ($id && !$valueFormatter->isDeactivated()) {
            $params['name'] = $id . "[{$objectId}-{$entryId}]";
        }

        if ($params['p_strValue'] instanceof \isys_callback) {
            $request = (new \isys_request())->set_category_data_id($valueFormatter->getEntryId())
                ->set_object_id($valueFormatter->getObjectId());

            $params['p_strValue'] = $params['p_strValue']->execute($request);
        } elseif ($value->getValue() !== $value->getViewValue()) {
            $params['p_strValue'] = $value->getViewValue();
        } else {
            $params['p_strValue'] = $value->getValue();
        }

        $params['p_bEditMode'] = true;
        $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-small', $params['p_strClass']) : 'input-small ') . " {$id}";

        $plugin = new isys_smarty_plugin_f_text();

        if ($valueFormatter->isDisabled()) {
            unset($params['p_strValue']);
            $params['p_bDisabled'] = true;
            $params['p_strClass'] .= ' multiedit-disabled ';
        }

        if ($params['name']) {
            $params['p_onChange'] = "window.multiEdit.changed(null, '{$params['name']}');";
        }

        if ($valueFormatter->isChangeAllRowsActive() && $valueFormatter->getPropertyKey() !== 'isys_cmdb_dao_category_g_global__sysid') {
            $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$id}', 'text');";
            unset($params['p_bDisabled']);
        }
        $params['inputGroupMarginClass'] = '';

        // @see ID-11425 Check, if the property should be shown as drop-down.
        $validationType = $property->getCheck()->getValidationType() ?? '';
        $validationOptions = $property->getCheck()->getValidationOptions();

        // Display the input field as select!
        $asSelect = $validationType === 'VALIDATE_BY_TEXTFIELD'
            && isset($validationOptions['as-select'])
            && $validationOptions['as-select'];

        if ($asSelect) {
            $params['force_dialog'] = true;

            if (isset($validationOptions['value'])) {
                $params['force_dialog_data'] = explode("\n", $validationOptions['value']);
            }
        }

        $type = self::$type;
        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>";
        $content .= $plugin->navigation_edit(\isys_application::instance()->container->get('template'), $params);
        $content .= '</td>';

        return $content;
    }

    /**
     * @param Value    $value
     * @param Property $property
     *
     * @return mixed|void
     */
    public static function checkFilter($value, $property)
    {
        // TODO: Implement checkFilter() method.
    }
}
