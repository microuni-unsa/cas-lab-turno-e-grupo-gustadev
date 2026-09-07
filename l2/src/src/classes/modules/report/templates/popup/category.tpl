<div id="popup-report-category">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_CATEGORIES"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content">
		<input type="hidden" name="report_id" value="[{$report_id}]">

		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="f_label" name="category_selection" ident="LC_UNIVERSAL__CATEGORY"}]</td>
				<td class="value">[{isys type="f_dialog" name="category_selection" p_bDbFieldNN=1 p_arData=$category_selection id="category_selection" p_strClass="input-small"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="category_title" ident="LC__REPORT__FORM__TITLE"}]</td>
				<td class="value">[{isys type="f_text" name="category_title" id="category_title" p_strClass="input-small"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="category_sort" ident="LC_UNIVERSAL__SORT"}]</td>
				<td class="value">[{isys type="f_count" name="category_sort" id="category_sort" p_strClass="input-mini" p_strValue=$latest_id}]</td>
			</tr>
			<tr>
				<td class="key vat">[{isys type="f_label" name="category_description" ident="LC__REPORT__FORM__DESCRIPTION"}]</td>
				<td class="value">[{isys type="f_textarea" id="category_description" name="category_description" p_nRows="5" p_strClass="input-small" p_strValue=$report_description}]</td>
			</tr>
		</table>
	</div>
	<div class="popup-footer-ng">
		[{isys name="save"
			type="f_button"
			id="save_button"
			icon="`$dir_images`axialis/basic/symbol-ok.svg"
			p_strValue="LC__UNIVERSAL__BUTTON_SAVE"}]

		[{isys name="delete"
			type="f_button"
			id="delete_button"
			icon="`$dir_images`axialis/industry-manufacturing/waste-bin.svg"
			p_strValue="LC__NAVIGATION__NAVBAR__DELETE"}]
	</div>
</div>

<style type="text/css">
	#popup-report-category .key {
		width: 100px;
	}
</style>

<script type="text/javascript">
	(function () {
		"use strict";

		var $popup = $('popup-report-category'),
			$categorySelection = $('category_selection');

        $popup.on('keydown', '#category_selection,#category_title,#category_sort', function (ev) {
            if (ev.key.toLowerCase() === 'enter') {
                ev.preventDefault();
            }
        })

		$popup.on('click', '.popup-closer', function () {
			popup_close();
		});

		var $buildTree = function () {
			new Ajax.Request('?ajax=1&call=report&func=build_tree', {
				parameters: {},
				method: "post",
				onSuccess: function (transport) {
					var json = transport.responseJSON;

					if (!!json.error) {
						idoit.Notify.error(json.message);
					} else {
						var menuTree = $('menuTreeJS');
						menuTree.update(json.message);
					}
				}
			});
		};

		$('delete_button').on('click', function () {
			if (confirm('[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_CATEGORIES__DELETE_CONFIRMATION" p_bHtmlEncode=false}]')) {
				new Ajax.Request('?ajax=1&call=report&func=delete_report_category', {
					parameters: {
						id: $categorySelection.getValue()
					},
					method: "post",
					onSuccess: function (transport) {
						var json = transport.responseJSON;

						if (!!json.error) {
							idoit.Notify.error(json.message);
						} else {
							idoit.Notify.success(json.message);
							$buildTree();
							$categorySelection.down(':selected').remove();
							$('category_title').setValue();
							$('category_description').setValue();
						}
					}
				});
			}
		});

		$('save_button').on('click', function () {
			if ($F('category_title').blank()) {
				idoit.Notify.error('[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_CATEGORY__ERROR_EMPTY_TITLE"}]');
			} else {
				idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__DATA_WAS_SAVED"}]');

				$('report_mode').setValue('category');
				$('navMode').setValue('[{$smarty.const.C__NAVMODE__SAVE}]');
				$('isys_form').submit();

				popup_close();
			}
		});

		$categorySelection.on('change', function () {
			var selected_category = $categorySelection.getValue();

			$('category_title').setValue('');
			$('category_description').setValue('');

			if (selected_category != '-1') {
				new Ajax.Request('?ajax=1&call=report&func=get_report_category', {
					parameters: {
						'id': selected_category
					},
					method: "post",
					onSuccess: function (transport) {
						var json = transport.responseJSON;

						$('category_title').setValue(json.isys_report_category__title);
						$('category_description').setValue(json.isys_report_category__description);
						$('category_sort').setValue(json.isys_report_category__sort);

						// This should be done with constants!
						if (json.isys_report_category__id != [{$smarty.const.C__REPORT_CATEGORY_GLOBAL}]) {
							$('save_button').enable();
							$('delete_button').enable();
							$popup.select('input,textarea').invoke('enable');
						} else {
							$('save_button').disable();
							$('delete_button').disable();
							$popup.select('input,textarea').invoke('disable');
						}
					}
				});
			} else {
				$('category_sort').setValue(parseInt('[{$latest_id}]') + 1);
				$popup.select('input,textarea').invoke('enable');
				$('save_button').enable();
				$('delete_button').disable();
			}
		});

		$categorySelection.simulate('change');
	}());

	[{if $force_close}]popup_close();[{/if}]
</script>
