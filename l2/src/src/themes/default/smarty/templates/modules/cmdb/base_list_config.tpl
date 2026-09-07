[{if isset($g_list)}]
	[{$g_list}]
[{else}]
	<input type="hidden" name="list_objtype_id" value="[{$list_obj_type_id}]">
    <input type="hidden" id="resetDefault" name="resetDefault" value="">

	<div class="p5">
		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" name="sorting_direction" ident="LC__REPORT__INFO__SORTING"}]</td>
				<td class="value">[{isys type="f_dialog" name="sorting_direction" p_bDbFieldNN='1' p_arData=$sorting_data p_strSelectedID=$defined_sorting p_bEditMode=1 p_strClass="input-mini" disableInputGroup=true}]</td>
			</tr>
			<tr>
				<td class="key">
                    [{isys type="f_label" name="grouping_type" ident="LC__MODULE__CMDB__GROUPING_TYPE"}]
                </td>
				<td class="value">
                    [{isys type="f_dialog" name="grouping_type" p_bDbFieldNN='1' p_arData=$groupingData p_strSelectedID=$groupingSelection p_bEditMode=1 p_strClass="input-small"}]
                    <div id="grouping_notice" class='box-blue p5 input-group input-size-medium' style="display:none; position:absolute; left:480px">[{isys type="lang" ident="LC__MODULE__CMDB__OBJECT_LIST_CONFIG_INFO_LIST_GROUPING"}]</div>
                </td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="default_filter_broadsearch" ident="LC__MODULE__CMDB__DEFAULT_FILTER_BROADSEARCH"}]</td>
				<td class="value pl20">[{isys type="checkbox" p_bInfoIconSpacer=0 name="default_filter_broadsearch" p_bEditMode=1 p_bChecked=$default_filter_broadsearch}]</td>
			</tr>
			<tr data-field="default-filter">
				<td class="key vat">
					[{isys type="f_label" name="default_filter_field" ident="LC__MODULE__CMDB__DEFAULT_FILTER"}]
					<img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam ml5" data-tip="<div class='box-blue p5'>[{isys type="lang" ident="LC__MODULE__CMDB__DEFAULT_FILTER_DESCRIPTION"}]</div>"/>
				</td>
				<td class="value">
					[{isys type="f_dialog" name="default_filter_field" p_arData=$defaultFilterFields p_strSelectedID=$defaultFilterField p_bEditMode=1 p_strClass="input-mini" p_bDbFieldNN=true}]
					<div id="filter_sub_value">
					[{isys type="f_text" name="default_filter_value" p_strValue=$defaultFilterValue p_bEditMode=1 p_strClass="input-mini hide"}]
					</div>
				</td>
			</tr>
			<tr>
				<td class="key">
					[{isys type="f_label" name="row_clickable" ident="LC__MODULE__CMDB__ROW_CLICK_FEATURE"}]
					<img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam ml5" data-tip="<div class='box-blue p5'>[{isys type="lang" ident="LC__MODULE__CMDB__ROW_CLICK_FEATURE_DESCRIPTION"}]</div>"/>
				</td>
				<td class="value pl20">[{isys type="checkbox" p_bInfoIconSpacer=0 name="row_clickable" p_bEditMode=1 p_bChecked=$row_clickable}]</td>
			</tr>
			<tr>
				<td class="key">
					[{isys type="f_label" name="default_filter_wildcard" ident="LC__MODULE__CMDB__DEFAULT_FILTER_WILDCARD"}]
					<img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam ml5" data-tip="<div class='box-blue p5'>[{isys type="lang" ident="LC__MODULE__CMDB__DEFAULT_FILTER_WILDCARD_DESCRIPTION"}]</span>"/>
				</td>
				<td class="value pl20">[{isys type="checkbox" p_bInfoIconSpacer=0 name="default_filter_wildcard" p_bEditMode=1 p_bChecked=$default_filter_wildcard}]</td>
			</tr>
            <tr>
                <td class="key">
                    [{isys type="f_label" name="show_email_links" ident="LC__MODULE__CMDB__SHOW_EMAIL_LINKS"}]
                    <img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam ml5" data-tip="<div class='box-blue p5'>[{isys type="lang" ident="LC__MODULE__CMDB__SHOW_EMAIL_LINKS_DESCRIPTION"}]</span>"/>
                </td>
                <td class="value pl20">[{isys type="checkbox" p_bInfoIconSpacer=0 name="show_email_links" p_bEditMode=1 p_bChecked=$show_email_links}]</td>
            </tr>
		</table>
		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" name="advanced_option_memory_unit" ident="LC__MODULE__CMDB__ADVANCED_OPTION__MEMORY_UNIT"}]</td>
				<td class="value">[{isys type="f_dialog" p_bSort=false name="advanced_option_memory_unit" p_bDbFieldNN='1' p_arData=$memory_unit_data p_strSelectedID=$defined_memory_unit p_bEditMode=1 p_strClass="input-mini" disableInputGroup=true}]</td>
			</tr>
		</table>
	</div>
	[{block name='properties'}]
	[{/block}]

	[{if $has_right_to_overwrite || $has_right_to_define_standard}]
	<hr class="mt10 mb10" />

	<table class="two-col" style="width:100%;">
		<tr>
			[{if $has_right_to_overwrite}]
			<td class="p5 vat">
				<div class="p5 bg-neutral-200 border">[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER"}]</div>
				<p class="mt5 mb5">[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_DESCRIPTION"}]</p>

				[{isys
					type="f_popup"
					p_strPopupType="browser_object_ng"
					name="C__CMDB__PERSON__SELECTION"
					catFilter="C__CATS__PERSON;C__CATS__PERSON_LOGIN"
					multiselection=true
					p_bInfoIconSpacer=0
					p_strClass="input-small"
					inputGroupMarginClass=""}]

				<button type="button" id="C__CMDB__BUTTON_SET_FOR_USER" class="ml20 btn">
					<img src="[{$dir_images}]axialis/basic/gear.svg" alt="" /><span>[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_BUTTON"}]</span>
				</button>
			</td>
			[{/if}]

			[{if $has_right_to_define_standard}]
			<td class="p5 vat">
				<div class="p5 bg-neutral-200 border">[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT"}]</div>
				<p class="mt5 mb5">[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT_DESCRIPTION"}]</p>
				<button type="button" id="C__CMDB__BUTTON_SET_AS_DEFAULT" class="btn">
                    <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" /><span>[{$define_as_standard_btn_label}]</span>
                </button>
			</td>
			[{/if}]
		</tr>
	</table>
	[{/if}]
    <style type="text/css">
        .contentTable td.key {
            width: 400px;
        }
    </style>

	<script type="text/javascript">
	(function () {
		'use strict';

        function handleBroadsearchCheckbox() {
            var $defaultFilterBroadsearch = $('default_filter_broadsearch');
            if ($defaultFilterBroadsearch.checked) {
                $$('[data-field="default-filter"] select, [data-field="default-filter"] input').invoke('setAttribute', 'disabled', 'disabled');
            } else {
                $$('[data-field="default-filter"] select, [data-field="default-filter"] input').invoke('removeAttribute', 'disabled');
            }
        }

		var $button_set_for_user = $('C__CMDB__BUTTON_SET_FOR_USER'),
			$button_set_as_default = $('C__CMDB__BUTTON_SET_AS_DEFAULT'),
			$personObjectSelection = $('C__CMDB__PERSON__SELECTION__HIDDEN'),
            $selectDefaultFilter = $('default_filter_field'),
            $groupingSelection = $('grouping_type');

        handleBroadsearchCheckbox();
        $('default_filter_broadsearch').on('change', handleBroadsearchCheckbox);

        $$('[data-tip]').each(function (element) {
            new Tip(element, element.getAttribute('data-tip'));
        });

        if ($groupingSelection) {
            $groupingSelection.on('change', () => {
                if ($groupingSelection.value == 1) {
                    $('grouping_notice').show();
                } else {
                    $('grouping_notice').hide();
                }
            });
            $groupingSelection.simulate('change');
        }

        if ($selectDefaultFilter) {
            var defaultFilterValueFirst = '[{$defaultFilterValue}]',
                defaultFilterFirst = '[{$defaultFilterField}]';

            $selectDefaultFilter.on('change', function() {
                var filterName = $selectDefaultFilter.value;

                if (!filterName) {
                    return;
                }

                var $defaultFilterValue = $('default_filter_value');

                $selectDefaultFilter.disable();
                $defaultFilterValue.disable();

                var filterValue = '';
                if (defaultFilterFirst == filterName) {
                    filterValue = defaultFilterValueFirst;
                }

                new Ajax.Request(window.www_dir + 'cmdb/list-config/getFilterProperty/' + filterName, {
                    parameters: {
                        filterValue: filterValue
                    },
                    onComplete: function (xhr) {

                        $selectDefaultFilter.enable();

                        var json = xhr.responseJSON;

                        if (!json || !json.success) {
                            $defaultFilterValue.enable();
                            $defaultFilterValue.removeClassName('hide');
                            idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                            return;
                        }
                        $('filter_sub_value').update(json.data);

                    }.bind(this)
                });
            });

            $selectDefaultFilter.simulate('change');
        }

		if ($button_set_for_user && $personObjectSelection) {
			$button_set_for_user.on('click', function () {
				var default_sorting = $('list_selection_field').down('input:checked');

				if ($personObjectSelection.getValue().blank()) {
					idoit.Notify.info('[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_NOTICE"}]', {life:10});
					return;
				}

				if (confirm('[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_CONFIRM" p_bHtmlEncode=false}]')) {
					$button_set_for_user.disable()
						.down('img').addClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/user-interface/loading.svg')
						.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

					new Ajax.Request('[{$ajax_url}]', {
						parameters: {
							'[{$smarty.const.C__GET__NAVMODE}]': '[{$smarty.const.C__NAVMODE__SAVE}]',
							list__HIDDEN: $('list__HIDDEN') ? $F('list__HIDDEN') : null,
                            list__HIDDEN_IDS: $('list__HIDDEN_IDS') ? $F('list__HIDDEN_IDS') : null,
							row_clickable: $('row_clickable').checked ? 'on' : '',
							default_sorting: (default_sorting ? default_sorting.getValue() : null),
							sorting_direction: $F('sorting_direction'),
							for_users: '1',
							users: $personObjectSelection.getValue(),
							object_type: '[{$objecttype.isys_obj_type__const}]',
							default_filter_wildcard: $('default_filter_wildcard').checked ? 'on' : '',
                            default_filter_field: $selectDefaultFilter.getValue(),
                            default_filter_value: $F('default_filter_value'),
                            grouping_type:$F('grouping_type'),
                            default_filter_broadsearch: $('default_filter_broadsearch').checked ? 'on': null,
                            show_email_links: $('show_email_links').checked ? 'on': null
						},
						onComplete: function (xhr) {
							// Nothing to do here. Notify popups will be triggered by PHP.
							$button_set_for_user.enable()
								.down('img').removeClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/basic/gear.svg')
								.next('span').update('[{isys type="lang" ident="LC__MODULE__CMDB__SET_FOR_USER_BUTTON"}]');
						}
					})
				}
			});
		}

		if ($button_set_as_default) {
			$button_set_as_default.on('click', function () {
				if (confirm('[{isys type="lang" ident="LC__MODULE__CMDB__SET_AS_DEFAULT_CONFIRM" p_bHtmlEncode=false}]')) {
                    var default_sorting = $('list_selection_field').down('input:checked');

					$button_set_as_default.disable()
						.down('img').addClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/user-interface/loading.svg')
						.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

					new Ajax.Request('[{$ajax_url}]', {
						parameters: {
							'[{$smarty.const.C__GET__NAVMODE}]': '[{$smarty.const.C__NAVMODE__SAVE}]',
                            list__HIDDEN: $('list__HIDDEN') ? $F('list__HIDDEN') : null,
                            list__HIDDEN_IDS: $('list__HIDDEN_IDS') ? $F('list__HIDDEN_IDS') : null,
							as_default: '1',
							object_type: '[{$objecttype.isys_obj_type__const}]',
							row_clickable: $('row_clickable').checked ? 'on' : '',
                            default_sorting: (default_sorting ? default_sorting.getValue() : null),
                            sorting_direction: $F('sorting_direction'),
							default_filter_wildcard: $('default_filter_wildcard').checked ? 'on' : '',
                            default_filter_field: $selectDefaultFilter.getValue(),
                            default_filter_value: $F('default_filter_value'),
                            grouping_type:$F('grouping_type'),
                            default_filter_broadsearch: $('default_filter_broadsearch').checked ? 'on': null,
                            show_email_links: $('show_email_links').checked ? 'on': null
						},
						onComplete: function (xhr) {
							// Nothing to do here. Notify popups will be triggered by PHP.
							$button_set_as_default.enable()
								.down('img').removeClassName('animation-rotate').writeAttribute('src', window.dir_images + 'axialis/basic/symbol-ok.svg')
								.next('span').update('[{$define_as_standard_btn_label|escape}]');
						}
					});
				}
			});
		}
		[{block 'extra_js'}]
		[{/block}]
	})();
	</script>
[{/if}]
