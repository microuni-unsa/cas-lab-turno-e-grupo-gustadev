<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Category\G\Location;

use idoit\Exception\Exception;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\DialogPlus;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_application;
use isys_cmdb_dao_category_s_enclosure;
use isys_smarty_plugin_f_dialog;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatSourceException;
use idoit\Module\Multiedit\Component\Multiedit\Exception\FormatCellException;

/**
 * Class DialogFormatter
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter
 */
class OptionFormatter extends Formatter implements FormatterInterface
{
    /**
     * @var string
     */
    protected static $type = C__PROPERTY__UI__TYPE__DIALOG;

    /**
     * @var bool
     */
    public static $changeAll = false;

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return Value
     */
    public static function formatSource($valueFormatter)
    {
        try {
            return DialogPlus::formatSource($valueFormatter);
        } catch (\Exception $e) {
            throw new FormatSourceException("Source Data for property: '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'Dialog'. Message: " . $e->getMessage());
        }
    }

    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $parentIsRack = false;
        $hasVerticalSlots = false;
        $value = ($valueFormatter->getValue() ?: (new Value()));
        $type = self::$type;
        $pluginContent = '';

        $dataset = $valueFormatter->getDataset();

        if (is_array($dataset) && isset($dataset['isys_cmdb_dao_category_g_location__parent']) && $dataset['isys_cmdb_dao_category_g_location__parent'] instanceof Value) {
            $parentObjectId = $dataset['isys_cmdb_dao_category_g_location__parent']->getValue();

            $dao = isys_cmdb_dao_category_s_enclosure::instance(isys_application::instance()->container->get('database'));

            $parentIsRack = $dao->objtype_is_cats_assigned($dao->get_objTypeID($parentObjectId), C__CATS__ENCLOSURE);

            if ($parentIsRack) {
                $data = $dao->get_data(null, $parentObjectId)->get_row();

                $hasVerticalSlots = $data['isys_cats_enclosure_list__vertical_slots_front'] > 0 || $data['isys_cats_enclosure_list__vertical_slots_rear'] > 0;
            }
        }

        $horizontalOption = (int)C__RACK_INSERTION__HORIZONTAL;
        $verticalOption = (int)C__RACK_INSERTION__VERTICAL;
        $insertionFront = (int)C__INSERTION__FRONT;
        $insertionRear = (int)C__INSERTION__REAR;
        $insertionBoth = (int)C__INSERTION__BOTH;

        try {
            $params = DialogPlus::cellParamsHelper($valueFormatter);
            unset($params['p_strPopupType']);

            $plugin = new isys_smarty_plugin_f_dialog();

            if (!$hasVerticalSlots && is_array($params['p_arData']) && isset($params['p_arData'][$verticalOption])) {
                unset($params['p_arData'][$verticalOption]);
            }

            if ($valueFormatter->isDisabled() || !$parentIsRack) {
                unset($params['p_strSelectedID'], $params['p_strValue']);
                $params['p_bDisabled'] = true;
                $params['p_strClass'] .= ' multiedit-disabled ';
            }

            if ($valueFormatter->isChangeAllRowsActive() && self::$changeAll && $params['name'] !== null) {
                $params['p_onChange'] .= ";window.multiEdit.overwriteAll(this, '{$valueFormatter->getPropertyKey()}', 'dialog');";
                unset($params['p_bDisabled']);
            }

            $language = isys_application::instance()->container->get('language');

            $params['p_onChange'] .=  ";const \$option = $('{$params['name']}');
                const \$insertion = $('{$params['name']}').up('tr').down('.isys_cmdb_dao_category_g_location__insertion');
                const \$position = $('{$params['name']}').up('tr').down('.isys_cmdb_dao_category_g_location__pos');

                \$position.disable();

                \$insertion
                    .enable()
                    .update(new Element('option', { value: -1 }).update(' - '))
                    .insert(new Element('option', { value: {$insertionFront} }).update('{$language->get("LC__CMDB__CATG__LOCATION_FRONT")}'))
                    .insert(new Element('option', { value: {$insertionRear} }).update('{$language->get("LC__CMDB__CATG__LOCATION_BACK")}'));

                if (\$option.getValue() == {$horizontalOption}) {
                    \$insertion.insert(new Element('option', { value: {$insertionBoth} }).update('{$language->get("LC__CMDB__CATG__LOCATION_BOTH")}'));
                }

                if (\$option.getValue() == -1) {
                    // Disable the next step, because we selected no value.
                    \$insertion.update(new Element('option', { value: -1 }).update(' - ')).disable();

                    if (\$insertion.next('input')) {
                        // Enable the 'hidden' field to transport an empty value.
                        \$insertion.next('input').enable();
                    }
                }

                if (\$insertion.next('input')) {
                    // Disable the 'hidden' field to not transport multiple values.
                    \$insertion.next('input').disable();
                }

                // Trigger the 'on change' handler of the insertion.
                \$insertion.simulate('change');";

            $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->get('template'), $params);
        } catch (\Exception $e) {
            throw new FormatCellException("Formating cell for property '{$valueFormatter->getPropertyKey()}' could not be handled for Formatter 'Dialog'. Message: " . $e->getMessage());
        }

        // Can not use sprintf because there is a problem with Strings which have '%' in it. See category property 'service_level'
        return "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>{$pluginContent}</td>";
    }
}
