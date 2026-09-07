<div id="popup-dialog-plus">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__POPUP__DIALOG_PLUS__TITLE"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

    <div class="popup-content">
        <div id="items">
            <!-- To be filled by JS -->
        </div>

        <div id="new-item">
            <div class="input-group input-size-block">
                [{isys type="f_text" name="popup-dialog-plus-new-value" p_strPlaceholder="LC__NAVIGATION__NAVBAR__NEW_TOOLTIP" p_bInfoIconSpacer=0 disableInputGroup=true}]

                <button type="button" id="popup-dialog-plus-add-new-value" class="btn input-group-addon input-group-addon-clickable">
                    <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" />
                    <span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
                </button>
            </div>
        </div>
    </div>

    <div class="popup-footer-ng">
        <button type="button" id="popup-dialog-plus-save" class="btn mr5">
            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_SAVE"}]</span>
        </button>
        <button type="button" class="btn popup-closer">
            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" class="mr5"/><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
        </button>
    </div>
</div>

<script type="text/javascript">
    // @todo  Refactor all of the JS into an own small class.

    (function () {
        "use strict";

        idoit.Translate.set('LC__CMDB__OBJECT_BROWSER__VALIDATION_EMPTY', '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__VALIDATION_EMPTY"}]');

        var $popup = $('popup-dialog-plus'),
            $input_new_value = $('popup-dialog-plus-new-value'),
            $button_new_value = $('popup-dialog-plus-add-new-value'),
            $button_save = $('popup-dialog-plus-save'),
            $items = $popup.down('#items'),
            objID = parseInt('[{$cat_table_object|default:0}]'),
            entryID = parseInt('[{$cat_table_entry|default:0}]');

        const editButtonHtml = new Element('button', { type:'button', className:'edit btn btn-secondary', title: '[{isys type="lang" ident="LC__UNIVERSAL__EDIT"}]', 'data-tooltip': 1})
            .update(new Element('img', { src:window.dir_images + 'axialis/basic/tool-pencil.svg', alt:'' }))
            .outerHTML;

        $input_new_value.on('keydown', function (ev) {
            if (ev.keyCode == Event.KEY_RETURN) {
                Event.stop(ev);
                idoit.callbackManager.triggerCallback('dialogplus_add_new_value');
            }
        });

        $button_new_value.on('click', function() {
            idoit.callbackManager.triggerCallback('dialogplus_add_new_value');
        });

        $button_save.on('click', function () {
            idoit.callbackManager.triggerCallback('dialogplus_save_and_close');
        });

        // Close the popup, when clicking ".popup-closer" elements.
        $popup.select('.popup-closer').invoke('on', 'click', function() {
            popup_close();
        });

        var item_div = $('items'),
            selected = $F('[{$self}]'),
            parent_id = 0, loadParameters;

        // @see  ID-5533  Don not process an empty selector.
        if (!'[{$parent}]'.blank() && $('[{$parent}]')) {
            parent_id = $F('[{$parent}]');
        }

        loadParameters = {
            'table':'[{$table}]',
            'parent_table':'[{$parent_table}]',
            'parent_table_id':parent_id,
            'condition': "[{$condition}]",
            'property': "[{$property}]"
        };

        var edit_dialog = function edit_dialog (ev) {
            var $el = ev.findElement(),
                div = $el.up('div'),
                title_span = div.down('span.value');

            if (! $el.up('div[data-id]')) {
                return;
            }

            if (! div.readAttribute('data-constant').blank()) {
                // This field holds a constant and may not be edited!
                return;
            }

            const editInput = new Element('input', {
                type:      'text',
                className: 'input input-small ml5',
                value:     (title_span.textContent || title_span.innerText || title_span.innerHTML),
                onKeyDown: 'if (event.keyCode == Event.KEY_RETURN) { Event.stop(event); }'
            });

            div.down('label').insert({ bottom: editInput });

            div.down('input.input').focus()

            // Remove the title span and the edit-icon.
            title_span.remove();
            Tips.hideAll();
            div.down('.edit').remove();

            // Restore the view.
            div
                .insert(new Element('button', { type: 'button', className: 'save btn', title: '[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]', 'data-tooltip': 1 })
                    .update(new Element('img', { src: window.dir_images + 'axialis/basic/symbol-ok.svg', className: 'greyscale', alt: '' })))
                .down('.save')
                .on('click', update_field);

            $('body').fire('update:tooltips');
        };

        var update_field = function update_field (ev) {
            var $div = ev.findElement('div'),
                title = $div.down('input.input').getValue();

            if (title.trim().replace(/[^\x00-\x7F]/g, "").length === 0) {
                idoit.Notify.error(idoit.Translate.get('LC__CMDB__OBJECT_BROWSER__VALIDATION_EMPTY'));
                return;
            }

            if ($div.readAttribute('data-id') == '-') {
                $div.down('input.input').remove();
                $div.down('.save').remove();

                $div.down('label').insert(new Element('span', {className: 'value ml5'}).update(title));
                $div.insert(editButtonHtml);
            } else {
                new Ajax.Request('?call=combobox&func=save_field&ajax=1',
                    {
                        parameters:{
                            'table':'[{$table}]',
                            'id': $div.readAttribute('data-id'),
                            'title': title
                        },
                        method:'post',
                        onSuccess:function (transport) {
                            var json = transport.responseJSON;

                            if (json.success)
                            {
                                $div.down('input.input').remove();
                                $div.down('.save').remove();

                                $div.down('label').insert(new Element('span', {className: 'value ml5'}).update(title));
                                $div.insert(editButtonHtml);
                            }
                            else
                            {
                                new Effect.Highlight($div, {startcolor: '#ffdddd', endcolor: '#ffffff'});
                            }
                        }.bind(this)
                    });
            }

            Tips.hideAll();
            $('body').fire('update:tooltips');
        };

        var add_new_value = function add_new_value () {
            var value = $input_new_value.getValue().strip(),
                radiobutton = $$('#items input[type="radio"]');

            if (! value.blank()) {
                // This is necessary for the IE. Yeah, I know...
                if (radiobutton && radiobutton.length > 0) {
                    radiobutton.each(function (el) {
                        if (el.hasAttribute('checked')) {
                            el.removeAttribute('checked');
                        }
                        el.checked = false;
                        el.simulate('blur');
                    });
                }

                item_div.insert(new Element('div', {'data-id':'-', 'data-constant': ''})
                    .update(new Element('label')
                        .update(new Element('input', {type:'radio', name:'selection', checked:true}))
                        .insert(new Element('span', {className:'ml5 value'}).update(value)))
                    .insert(editButtonHtml)
                );

                $input_new_value.setValue('');

                [{if $multiselect}]
                $popup.select('input[type="radio"]').invoke('hide').invoke('setValue', '');
                [{/if}]
            }

            $input_new_value.focus();
            $('body').fire('update:tooltips');
        };

        var save_and_close = function save_and_close () {
            var items = [],
                classIterator,
                url = '?call=combobox&func=save&ajax=1',
                parameters;

            // Save all changes if the save for each field has not been triggered
            item_div.select('div').each(function ($div) {
                if ($div.down('input.input')) {
                    $div.down('.save').simulate('click');
                }

                // At first we gather all the elements (including their sorting).
                items.push({
                    'id': $div.readAttribute('data-id'),
                    'name': $div.down('span').textContent || $div.down('span').innerHTML,
                    'checked': $div.down('input').checked
                });
            }.bind(this));

            classIterator = $('[{$self}]').name.replace(/\[.*/, '');

            parameters = {
                'parent': '{"selected_id":' + parent_id + ',"table":"[{$parent_table}]"}',
                'data': Object.toJSON(items),
                'table': '[{$table}]',
                'condition': "[{$condition}]"
            };

            // This will be used when filling CMDB categories by dialog+.
            if (objID > 0) {
                url = '?call=combobox&func=save_cat_data&ajax=1';
                parameters.cat_table_object = objID;
                loadParameters.cat_table_object = objID;
            }

            if (entryID > 0) {
                loadParameters.cat_table_entry = entryID;
            }

            // And now we save the data.
            new Ajax.Request(url, {
                parameters:parameters,
                method:'post',
                onSuccess:function (transport) {
                    var callback_func = "[{$callback_accept}]",
                        selected_id = transport.responseText,
                        self = $('[{$self}]'),
                        customDialog = self.name.includes('C__CATG__CUSTOM'),
                        current_id = $('[{$self}]').getValue();

                    new Ajax.Request('?call=combobox&func=load&ajax=1', {
                        parameters:loadParameters,
                        method:'post',
                        onSuccess:function (transport) {
                            var json = [],
                                option_ids = [],
                                index = 0,
                                checkedIds = [],
                                initialSelection = self.getValue();

                            // Empty content for sbox
                            self.update('');

                            // Transform to json
                            if (transport.responseText != '[]') {
                                json = $H(transport.responseJSON);
                            }

                            // Add null parameter
                            [{if ! $notnull_parameter}]
                            self.insert(new Element('option', {value: '-1'}).update('-'));
                            index ++;
                            [{/if}]

                            // logic for checkboxes table
                            var customFieldKey = self.id.replace('C__CATG__CUSTOM__', '');
                            var isCheckboxesSelect = (self.getAttribute('data-output-type') === 'checkboxes');
                            var $checkboxList = null;

                            if (isCheckboxesSelect) {
                                $checkboxList = $('checkboxesSelection__C__CATG__CUSTOM__' + customFieldKey);

                                // Save previously checked values.
                                checkedIds = $checkboxList.select('input:checked').invoke('getValue');
                                $checkboxList.update('');
                            }

                            // Add options to sbox
                            json.each(function(item) {
                                var itemkey = item.key.replace(/^\s+|\s+$/g, '');

                                option_ids.push(itemkey);

                                // logic for updating list of checkboxes in checkboxes table
                                var checked = checkedIds.includes(itemkey);

                                if ($checkboxList) {
                                    var checkboxId = 'checkbox__C__CATG__CUSTOM__' + itemkey + '__' + item.value;

                                    $checkboxList.insert(
                                        new Element('li', { className: 'mb5' })
                                            .update(new Element('label')
                                                .update(new Element('input', { className:'C__CATG__CUSTOM__' + customFieldKey + ' mr5', type: 'checkbox', id: checkboxId, name: 'C__CATG__CUSTOM__' + customFieldKey + '[' + index + ']', value: itemkey, checked: checkedIds.includes(itemkey) }))
                                                .insert(new Element('span').update(item.value))
                                            ));
                                }

                                var option = new Element('option', {value: itemkey}).update(item.value);
                                if (checked) {
                                    option.setAttribute('selected', 'selected');
                                }
                                self.insert(option);

                                if (selected_id == itemkey) {
                                    // Set value
                                    self.setValue(itemkey);

                                    /*
                                     * Let us check for changed selection before triggering change event to fill the child.
                                     * Otherwise we would lose selection in child field
                                     */
                                    [{if ('[{$child}]' != '' && '[{$child_table}]')}]
                                    if (selected_id != current_id) {
                                        self.simulate('change');
                                    }
                                    [{/if}]
                                }
                                index++;
                            });

                            // Set initial values
                            if (!isCheckboxesSelect) {
                                self.setValue(parseInt(selected_id) > 0 ? selected_id : initialSelection);
                            }

                            if (customDialog) {
                                self.fire('custom-event:updated');
                            }
                            // ID-2822 Bugfix
                            self.fire('chosen:updated');

                            // Fire Custom Dialog Plus After Save Event
                            self.fire('dialog-plus:afterSave', {
                                'classIterator': classIterator,
                                'selectBox': self,
                                'options': json,
                                'parent': [{if !$parent}]0[{else}]1[{/if}]
                            });

                            [{if $onComplete|default:FALSE}][{$onComplete}][{/if}]
                        }
                    });

                    try {
                        if (callback_func != '') {
                            eval(callback_func);
                        }
                    } catch (e) {
                        idoit.Notify.error(e);
                    }

                    popup_close();
                }.bind(this)
            });
        }.bind(this);

        // Load the items.
        new Ajax.Request('?call=combobox&func=load_extended&ajax=1',
            {
                parameters:{
                    'table':'[{$table}]',
                    'parent_table':'[{$parent_table}]',
                    'parent_table_id':parent_id,
                    'condition': "[{$condition}]",
                    'property': "[{$property}]"
                },
                method:'post',
                onSuccess:function (transport) {
                    var json = transport.responseJSON,
                        index,
                        item,
                        $edit;

                    // When we get no data, we should not run the rest of the code.
                    if (transport.responseText == '[]') {
                        return null;
                    }

                    for (index in json) {
                        if (json.hasOwnProperty(index)) {
                            item = json[index];
                            index = index.replace(/\s+$/,'');

                            item_div.insert(new Element('div', { 'data-id':index, 'data-constant':item.constant})
                                .update(new Element('label')
                                    .update(new Element('input', {type:'radio', name:'selection', checked:(index == selected)}))
                                    .insert(new Element('span', {className:'value ml5'}).update(item.title || '-')))
                                .insert(item.constant == '' ? editButtonHtml : ''));
                        }
                    }

                    [{if $multiselect}]
                    $popup.select('input[type="radio"]').invoke('hide').invoke('setValue', '');
                    [{/if}]
                    $('body').fire('update:tooltips');
                }
            });

        // Focus the input-field for direct input.
        $input_new_value.focus();

        $items.on('click', '.edit', edit_dialog);
        $items.on('dblclick', 'span.value', edit_dialog);

        idoit.callbackManager
            .registerCallback('dialogplus_add_new_value', add_new_value)
            .registerCallback('dialogplus_save_and_close', save_and_close);
    }());
</script>
