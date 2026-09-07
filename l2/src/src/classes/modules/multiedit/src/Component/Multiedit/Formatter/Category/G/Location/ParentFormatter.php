<?php

namespace idoit\Module\Multiedit\Component\Multiedit\Formatter\Category\G\Location;

use idoit\Component\Property\Property;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Formatter;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterManager;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\FormatterInterface;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup\ObjectBrowser;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\Value;
use idoit\Module\Multiedit\Component\Multiedit\Formatter\ValueFormatter;
use isys_callback;
use isys_cmdb_dao_category;
use isys_popup_browser_object_ng;
use isys_request;
use isys_smarty_plugin_f_popup;
use idoit\Exception\Exception;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserReceiver;

/**
 * Class ObjectBrowser
 *
 * @package idoit\Module\Multiedit\Component\Multiedit\Formatter\Popup
 */
class ParentFormatter extends ObjectBrowser implements FormatterInterface
{
    /**
     * @param ValueFormatter $valueFormatter
     *
     * @return string|void
     * @throws \Exception
     */
    public static function formatCell($valueFormatter)
    {
        $property = $valueFormatter->getProperty();
        $value = ($valueFormatter->getValue() ?: (new Value()));
        $objectId = $valueFormatter->getObjectId();
        $entryId = $valueFormatter->getEntryId();
        $type = self::$type;

        $content = "<td data-cell-type='{$type}' data-old-value='{$value->getViewValue()}' data-sort='{$value->getViewValue()}' data-key='{$valueFormatter->getPropertyKey()}' class='multiedit-table-td'>%s</td>";

        $params = $property->getUi()
            ->getParams();
        $params['name'] = null;
        $id = $valueFormatter->getPropertyKey();

        if ($id && !$valueFormatter->isDeactivated()) {
            $params['name'] = $id . "[{$objectId}-{$entryId}]";
        }

        if (!$value->getValue()) {
            $request = (new \isys_request())->set_category_data_id($valueFormatter->getEntryId())
                ->set_object_id($valueFormatter->getObjectId());

            if ($params['p_arData'] instanceof \isys_callback) {
                $params['p_arData'] = $params['p_arData']->execute($request);
            }
            if ($params['p_strSelectedID'] instanceof \isys_callback) {
                $params['p_strSelectedID'] = $params['p_strSelectedID']->execute($request);

                if (is_object($params['p_strSelectedID'])) {
                    unset($params['p_strSelectedID']);
                }
            }
            if ($params['p_strValue'] instanceof \isys_callback) {
                $params['p_strValue'] = $params['p_strValue']->execute($request);

                if (is_object($params['p_strValue'])) {
                    unset($params['p_strValue']);
                }
            }

            if (isset($params['p_strPrim'])) {
                unset($params['p_strPrim']);
            }
        } else {
            $params['p_strSelectedID'] = $value->getValue();
            unset($params['p_strValue']);
        }

        $elementId = "{$id}__HIDDEN[{$objectId}-{$entryId}]";
        $disableOptions =  "$('{$elementId}').up('tr')
                .select('.isys_cmdb_dao_category_g_location__option,.isys_cmdb_dao_category_g_location__insertion,.isys_cmdb_dao_category_g_location__pos')
                .invoke('disable')
                .invoke('update', new Element('option', { value: -1 }).update(' - ').outerHTML);";

        $reloadOptions =  "const \$parent = $('{$elementId}');
            const \$insertion = $('{$elementId}').up('tr').down('.isys_cmdb_dao_category_g_location__option').disable();

            {$disableOptions}

            new Ajax.Request(window.www_dir + '?ajax=1&call=rack&func=get_immediate_parent_rack', {
                parameters: { 'obj_id':   \$parent.getValue() },
                method:     'post',
                onSuccess:  function (xhr) {
                    if (xhr.responseJSON.data > 0) {
                        \$insertion.enable();

                        if (\$insertion.next('input')) {
                            // Disable the next step, because we selected no value.
                            \$insertion.next('input').disable();
                        }

                        new Ajax.Request(window.www_dir + '?ajax=1&call=rack&func=get_rack_options', {
                            parameters: { 'obj_id': xhr.responseJSON.data },
                            method:     'post',
                            onSuccess:  function (xhr) {
                                \$insertion.update(new Element('option', { value: -1 }).update(' - '));

                                if (!Array.isArray(xhr.responseJSON)) {
                                    return;
                                }

                                for (i in xhr.responseJSON) {
                                    if (! xhr.responseJSON.hasOwnProperty(i)) {
                                        continue;
                                    }

                                    \$insertion.insert(new Element('option', { value: xhr.responseJSON[i].id }).update(xhr.responseJSON[i].title));
                                }
                            }
                        });
                    } else {
                        if (\$insertion.next('input')) {
                            // Enable the 'hidden' field to transport an empty value.
                            \$insertion.next('input').enable();
                        }
                    }
                }
            });";

        if ($params['name']) {
            $params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] = "window.multiEdit.changed(null, '{$params['name']}');{$reloadOptions}";
            $params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] = "window.multiEdit.changed(null, '{$params['name']}');{$disableOptions}";
            $params['p_onChange'] = "window.multiEdit.changed(null, '{$params['name']}');";
        }

        $params['p_strPopupType'] = (isset($params['p_strPopupType']) ? $params['p_strPopupType'] : 'browser_object_ng');
        $params[isys_popup_browser_object_ng::C__EDIT_MODE] = true;
        $params['edit'] = true;
        $params['p_strClass'] = ($params['p_strClass'] ? preg_replace('/(input-[a-z0-9]*)/', 'input-block', $params['p_strClass']) : 'input-block ') . " {$id}";
        $params['inputGroupMarginClass'] = '';

        if ($valueFormatter->isChangeAllRowsActive() && $params['name'] !== null) {
            $params[isys_popup_browser_object_ng::C__CALLBACK__ACCEPT] .= "window.multiEdit.overwriteAll(null, '{$valueFormatter->getPropertyKey()}', 'objectBrowser');";
            $params[isys_popup_browser_object_ng::C__CALLBACK__DETACH] .= "window.multiEdit.overwriteAll(null, '{$valueFormatter->getPropertyKey()}', 'objectBrowser');";
        } elseif (strpos($params['name'], '[-]') === false) {
            $referenceIdentifier = 'isys_cmdb_dao_category_g_location__parent[-]';
            // We need the timeout because the 'parent' frontend value needs to be set before calling the callback.
            $reloadOptions = "setTimeout(function(){ {$reloadOptions}; }, 10);";

            $register = \isys_register::factory('callbacks');

            if ($register->has($referenceIdentifier)) {
                $callbackArr = $register->get($referenceIdentifier);
                $callbackArr[] = $reloadOptions;
                $register->set($referenceIdentifier, $callbackArr);
            } else {
                $register->set($referenceIdentifier, [$reloadOptions]);
            }
        }

        unset($params[isys_popup_browser_object_ng::C__FORM_SUBMIT], $params[isys_popup_browser_object_ng::C__RETURN_ELEMENT]);

        $plugin = new isys_smarty_plugin_f_popup();
        $pluginContent = $plugin->navigation_edit(\isys_application::instance()->container->get('template'), $params);

        return sprintf($content, $pluginContent);
    }
}
