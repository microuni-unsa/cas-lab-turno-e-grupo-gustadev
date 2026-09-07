<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Row;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ObjectInfoFormatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_application;
use isys_smarty_plugin_f_button;

/**
 * Class HeaderRow
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Row
 */
class HeaderRow extends Row implements RowInterface
{
    /**
     * @return string
     * @throws \Exception
     */
    public function render()
    {
        $properties = current($this->getProperties()
            ->getData());
        $data = $this->getData();
        $language = isys_application::instance()->container->get('language');
        $wwwDir = isys_application::instance()->www_path;

        $content = '<thead><tr>';
        $pattern = '<th %s><div>%s %s</div></th>';

        $sortIcon = "<img class='m5 opacity-30 multiedit-header-sort-img' src='{$wwwDir}images/axialis/user-interface/arrow-up.svg' />";

        $content .= sprintf(
            $pattern,
            'class="multiedit-td-action"',
            '',
            "<img id='multiEditLoader' alt='{$language->get('LC__UNIVERSAL__LOADING')}' src='{$wwwDir}images/axialis/user-interface/loading.svg' class='animation-rotate' />"
        );
        $content .= sprintf(
            $pattern,
            'enable-sort="1" class="multiedit-td-object-title mouse-pointer multiedit-header-sort-asc" data-key="object-title" onclick="window.multiEdit.sortContent(this, \'object-title\')"',
            '<span class="mr-auto">' . $language->get('LC__UNIVERSAL__OBJECT_TITLE') . '</span>',
            $sortIcon
        );

        $options = [
            'p_strClass'        => 'btn-small ml-auto',
            'icon'              => $wwwDir . 'images/axialis/basic/eye-no.svg',
            'p_bInfoIconSpacer' => 0,
            'type'              => 'button',
            'p_strTitle'        => $language->get('LC__UNIVERSAL__HIDE'),
            'p_strValue'        => '',
            'inputGroupMarginClass' => '',
            'p_onMouseOver' => 'window.multiEdit.disableSort(this);',
            'p_onMouseOut' => 'window.multiEdit.enableSort(this);'
        ];

        foreach ($properties as $propertyKey => $property) {
            $options['p_onClick'] = "window.multiEdit.disableColumn('{$propertyKey}')";
            $hideColumnBtn = (new isys_smarty_plugin_f_button())->navigation_edit(isys_application::instance()->container->get('template'), $options);

            $content .= sprintf(
                $pattern,
                'class="mouse-pointer multiedit-header-sort-asc" enable-sort=1 data-key="' . $propertyKey . '" onclick="window.multiEdit.sortContent(this, \'' . $propertyKey . '\')"',
                '<span>' . $language->get($property->getInfo()->getTitle()) . '</span>',
                $hideColumnBtn .
                $sortIcon
            );
        }

        return $content . '</tr></thead>';
    }
}
