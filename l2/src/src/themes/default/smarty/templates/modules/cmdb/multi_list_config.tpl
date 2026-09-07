[{extends './base_list_config.tpl'}]
[{block 'properties'}]
    <div class="property-selector p5">
        <table class="w100" style="table-layout: fixed; max-width: 850px;" cellspacing="0">
            <tr>
                <td class="vat">
                    <div class="mr10 border bg-white" style="width: 400px">
                        <div class="m0 p10 bg-neutral-200 border-bottom browser-tabs">
                            [{isys type="lang" ident="LC__REPORT__INFO__ATTRIBUTE_CHOOSER_HEADLINE"}]
                        </div>
                        <div class="m10" id="property_list">
                            [{foreach $properties as $property}]
                            <div class="category-field [{if isset($selected_properties[$property->getPropertyKey()]) }]hide[{/if}]" data-key="[{$property->getPropertyKey()}]" [{if $property->isIndexed() || $property->isFilterable()}]data-indexed="1"[{/if}]>
                                <span>[{isys type="lang" ident=$property->getName()}]</span>
                                [{if $property->isIndexed() || $property->isFilterable() }]
                                <span style="float: right; margin-right: 20px;">
	                                <input type="radio" class="hide" name="default_sorting" disabled="disabled" value="[{$property->getPropertyKey()}]" style="margin-top: 2px;">
	                            </span>
                                [{/if}]
                                <span class="plus"></span>
                            </div>
                            [{/foreach}]
                            <div class="category-field empty [{if count($selected_properties) !== count($properties) }]hide[{/if}]">[{isys type="lang" ident="LC__REPORT__NO_ATTRIBUTES_FOUND"}]</div>
                        </div>
                    </div>
                </td>
                <td class="vat">
                    <div class="border bg-white fl" style="width: 400px">
                        <div class="m0 p10 bg-neutral-200 border-bottom browser-tabs">
                            [{isys type="lang" ident="LC__REPORT__INFO__CHOSEN_PROPERTIES_TEXT"}]
                            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" id="default-deselector" class="fr mr5 mouse-pointer hide" title="[{isys type="lang" ident="LC__UNIVERSAL__DESELECT"}]" />
                        </div>
                        <div class="m10" id="list_selection_field" style="position: relative;">
                            <input id="list__HIDDEN" name="list__HIDDEN" type="hidden" value="[{array_keys($selected_properties)|json_encode}]" />
                            <div class="empty category-field [{if !is_array($selected_properties) || count($selected_properties) == 0 }]hide[{/if}]">[{isys type="lang" ident="LC__REPORT__NO_ATTRIBUTES_ADDED"}]</div>
                            <div id="list_selected_properties" class="draggable" />
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
[{/block}]

[{block 'extra_js'}]
    var selected      = JSON.parse('[{array_keys($selected_properties)|json_encode}]'),
        total         = [{count($properties)}],
        list          = $('property_list'),
        selectedList  = $('list_selected_properties'),
        $deselector   = $('default-deselector'),
        selectedEmpty = $('list_selection_field').down('.empty'),
        listEmpty     = list.down('.empty'),
        value         = $('list_selection_field').down('[name="list__HIDDEN"]'),
        defaultFilter = $('default_filter_field');

    Position.includeScrollOffsets = true;

    function redrawProperties(defaultSorting) {
        var original, cloned, option,
            sorting = $$('[name="default_sorting"]:checked').invoke('getValue')[0] || defaultSorting,
            filter  = defaultFilter.getValue();

        if (sorting) {
            $deselector.removeClassName('hide');
        }

        value.setValue(JSON.stringify(selected));

        list.select('[data-key]').forEach(function (a) {
            a.removeClassName('hide');
        });

        selectedList.innerHTML = '';
        defaultFilter.innerHTML = '';

        for (var i = 0; i < selected.length; ++i) {
            original = list.down('[data-key="' + selected[i] + '"]');

            if (!original) {
                continue;
            }

            cloned = original.cloneNode(true);
            cloned.removeClassName('category-field');
            cloned.addClassName('selected-field property');
            cloned.down('.plus').removeClassName('plus').addClassName('minus');

            if (cloned.down('[name="default_sorting"]')) {
                cloned.down('[name="default_sorting"]').removeAttribute('disabled');
                cloned.down('[name="default_sorting"]').removeClassName('hide');
                cloned.down('[name="default_sorting"]').on('change', function () {
                    $deselector.removeClassName('hide');
                });
            }

            if (cloned.getAttribute('data-indexed')) {
                option = document.createElement('option');
                option.innerHTML = cloned.innerText.trim();
                option.value = cloned.getAttribute('data-key');
                defaultFilter.insert(option);
            }

            selectedList.insert(cloned);
            original.addClassName('hide');
        }

        defaultFilter.setValue(filter);
        defaultFilter.simulate('change');

        if (selected.length == 0) {
            selectedEmpty.removeClassName('hide');
        } else {
            selectedEmpty.addClassName('hide');
        }

        if (selected.length == total) {
            listEmpty.removeClassName('hide');
        } else {
            listEmpty.addClassName('hide');
        }

        if (sorting && $('list_selection_field').down('[name="default_sorting"][value="' + sorting + '"]')) {
            $('list_selection_field').down('[name="default_sorting"][value="' + sorting + '"]').checked = true;
        }

        Sortable.create('list_selected_properties', {
            tag:      'div',
            dragOnEnd: function () {
                selected = selectedList.select('[data-key]').invoke('getAttribute', 'data-key');
                redrawProperties();
            },
            dragSnap: (x, y, drag) => [0, y + drag.offset[1] + $('contentWrapper').scrollTop - 12]
        });
    }

    $deselector.on('click', function () {
        var $selectedRadio = selectedList.down(':checked');

        if ($selectedRadio) {
            $selectedRadio.setValue(0);
        }

        $deselector.addClassName('hide');
    });

    list.on('click', '.plus', function (e) {
        var element = e.findElement('[data-key]'),
            key     = element.getAttribute('data-key');
        selected.push(key);
        redrawProperties();
    });

    selectedList.on('click', '.minus', function (e) {
        var element = e.findElement('[data-key]'),
            key     = element.getAttribute('data-key'),
            index   = selected.indexOf(key);

        if (index >= 0) {
            selected.splice(index, 1);
            redrawProperties();
        }
    });

    redrawProperties('[{$default_sorting}]');
[{/block}]
