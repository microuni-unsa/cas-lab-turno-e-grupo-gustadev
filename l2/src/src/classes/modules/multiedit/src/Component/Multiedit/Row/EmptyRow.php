<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Module\Multiedit\Component\Multiedit\Formatter\ObjectInfoFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use isys_cmdb_dao_category_g_custom_fields;
use isys_smarty_plugin_f_button;

/**
 * Class EmptyRow
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
class EmptyRow extends Row implements RowInterface
{
    /**
     * @var array
     */
    private static $usedEntryIds = [];

    /**
     * @return string
     */
    public function render()
    {
        global $g_dirs;

        $propertiesSource = $this->getProperties();
        $propertiesCount = $propertiesSource->count();
        $properties = current($propertiesSource->getData());
        $dao = $propertiesSource->getDao();

        $language = \isys_application::instance()->container->get('language');

        $propertyKey = key($properties);
        //$daoClass = substr($propertyKey, 0, strpos($propertyKey, '__'));
        $daoClass = get_class($dao);
        $data = $this->getData();

        /**
         * @var $id string
         */
        do {
            $id = preg_replace('/[^a-zA-Z0-9]./', '', \isys_helper_crypt::encrypt(mt_rand(0, 10000)));
        } while (isset(self::$usedEntryIds[$id]));

        self::$usedEntryIds[$id] = true;
        $entryId = 'new-' . $id;

        $valueFormater = (new ValueFormatter())->setEntryId($entryId)
            ->setObjectId($this->getObjectId());

        $message = $language->get('LC__MODULE__MULTIEDIT__OBJECT_HAS_NO_DATA_IN_CATEGORY');
        $iconSrc = $g_dirs['images'] . '/axialis/basic/button-info.svg';

        $plugin = new isys_smarty_plugin_f_button();

        $categoryType = $dao->get_category_type();
        if ($daoClass === 'isys_cmdb_dao_category_g_custom_fields') {
            $categoryId = $dao->get_catg_custom_id();
        } else {
            $categoryId = $dao->get_category_id();
        }
        $categoryIdentifier = $categoryType . '_' . $categoryId;

        $params = [
            'type'       => 'f_button',
            'p_strValue' => 'LC__MODULE__MULTIEDIT__CREATE_NEW_VALUE',
            'p_strClass' => 'new-entry ml10',
            'p_onClick'  => "window.multiEdit.addNewEntry('{$daoClass}', {$this->getObjectId()}, '{$entryId}', '{$categoryIdentifier}');"
        ];
        $createButton = $plugin->navigation_edit(\isys_application::instance()->container->get('template'), $params);
        $trClassName = 'emptyValue';

        if ($data === false) {
            // Category is not assigned so no entry can be created
            $message = $language->get('LC__MODULE__MULTIEDIT__SELECTED_CATEGORY_IS_NOT_ASSIGNED_TO_OBJECT');
            $iconSrc = $g_dirs['images'] . '/axialis/basic/button-cancel.svg';
            $createButton = '';
            $trClassName = '';
        } elseif ($data === true) {
            // User has no rights for thies object/entry
            $message = $language->get('LC__MODULE__MULTIEDIT__NO_RIGHTS');
            $iconSrc = $g_dirs['images'] . '/axialis/basic/button-cancel.svg';
            $createButton = '';
            $trClassName = '';
        }

        return "<tr class='{$trClassName}' id='object-row_{$this->getObjectId()}-{$entryId}' data-entry='{$entryId}'>" .
            '<td class="multiedit-td-action"></td>' .
            ObjectInfoFormatter::formatCell((new ValueFormatter())->setValue($this->getObjectData())) .
            "<td class='multiedit-td-create-new' colspan='{$propertiesCount}'>" .
            "<div><img src='{$iconSrc}' class='mr5' /><span>{$message}</span> {$createButton}</div>" .
            '</td>' .
            '</tr>';
    }
}
