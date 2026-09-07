<div id="popup-duplicate">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__CATG__ODEP_OBJ"}] [{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DUPLICATE"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content">
        [{if $isAllowedToDuplicate}]
		[{if !$customName}]
		<div class="m5 p5 text-blue display-flex align-items-center">
			<img src="[{$dir_images}]axialis/basic/button-info.svg" alt="info" class="mr5" /><span>[{isys type="lang" ident="LC__POPUP__DUPLICATE__POST_NEW_NAME"}]</span>
		</div>
		[{/if}]

		<table class="contentTable">
			[{if $customName}]
			<tr>
				<td class="key">[{isys type="f_label" name="object_title" ident="LC__CMDB__DUPLICATE__NEW_NAME"}]</td>
				<td class="value">[{isys type="f_text" name="object_title" id="object_title" p_strClass="input-small" p_strValue=$object_title}]</td>
			</tr>
			[{/if}]
			<tr>
				<td class="key">
					[{isys type='f_label' name='update_globals' ident='LC_UNIVERSAL__UPDATE'}]
					<img title="[{isys type="lang" ident="LC__CMDB__DUPLICATE__UPDATE_GLOBALS"}]" alt="help" src="[{$dir_images}]axialis/basic/button-help.svg" data-tooltip="1" class="vam ml5"/>
				</td>
				<td class="value"><input type="checkbox" checked="checked" name="update_globals" id="update_globals" value="1" class="ml20" /></td>
			</tr>
			[{if is_array($specificCategories) && count($specificCategories) > 0}]
			<tr>
				<td class="key vat pt5">[{isys type="lang" ident="LC__CMDB__DUPLICATE__SPECIFIC_CATEGORIES"}]</td>
				<td class="value">
					<label class="pl20 display-block text-bold">
						<input type="checkbox" checked="checked" id="select_all_cats" name="select_all_cats" class="mr5 select_all_categories" value="-1"/>
						[{isys type="lang" ident="LC__UNIVERSAL__SELECT_ALL"}]
					</label>

					<div class="pl15 mt15 category-checkbox-list">
						[{foreach $specificCategories as $cat}]
						<label class="fl p5 display-flex align-items-center" title="[{$cat.title}]">
							<input type="checkbox" class="categories mr5" checked="checked" name="specificCategory[]" value="[{$cat.id}]"/>
                            <span>[{$cat.title}]</span>
						</label>
						[{/foreach}]
					</div>
				</td>
			</tr>
			[{/if}]
			<tr>
				<td class="key vat pt5">[{isys type="lang" ident="LC__CMDB__DUPLICATE__GLOBAL_CATEGORIES"}]</td>
				<td class="value">
					<label class="pl20 display-block text-bold">
						<input type="checkbox" checked="checked" id="select_all_catg" name="select_all_catg" class="mr5 select_all_categories" value="-1"/>
						[{isys type="lang" ident="LC__UNIVERSAL__SELECT_ALL"}]
					</label>

					<div class="pl15 mt15 category-checkbox-list" style="max-height:250px;">
						[{foreach $categories as $cat}]
							<label class="fl p5 display-flex align-items-center" title="[{$cat.title}]">
								<input type="checkbox" class="categories mr5" checked="checked" name="globalCategory[]" value="[{$cat.id}]"/>
                                <span>[{$cat.title}]</span>
							</label>
						[{/foreach}]

						[{if is_array($custom_categories) && count($custom_categories)}]
						<br class="cb" />
						<h3 class="mt20">[{isys type="lang" ident="LC__CMDB__CUSTOM_CATEGORIES"}]</h3>
						[{foreach $custom_categories as $cat}]
							<label class="fl p5 display-flex align-items-center" title="[{$cat.title}]">
								<input type="checkbox" class="categories mr5" checked="checked" name="custom_category[]" value="[{$cat.id}]"/>
                                <span>[{$cat.title|escape}]</span>
							</label>
						[{/foreach}]
						[{/if}]
					</div>
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="duplicate_options" ident="LC__MASS_CHANGE__OPTIONS"}]</td>
				<td class="value">
					<select name="duplicate_options" id="duplicate_options" class="input input-small ml20">
						<option value="0">-</option>
						<optgroup label="Virtuelle Maschine">
							<option value="virtualize">[{isys type="lang" ident="LC__CMDB__DUPLICATE__VIRTUALIZE"}]</option>
							<option value="devirtualize">[{isys type="lang" ident="LC__CMDB__DUPLICATE__DEVIRTUALIZE"}]</option>
						</optgroup>
					</select>
				</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="open_new_created_object" ident="LC__MASS_CHANGE__OPEN_NEWLY_CREATED_OBJECT"}]</td>
				<td class="value">
					[{isys type="checkbox" name="open_new_created_object"}]
				</td>
			</tr>
		</table>
        [{else}]
            <p class="box-red m5 p5">
                <img src="[{$dir_images}]icons/silk/delete.png" class="mr5 vam" />
                <span class="vam">[{$missingRightTranslation}]</span>
            </p>
        [{/if}]
	</div>

	<div class="popup-footer-ng">
        [{if $isAllowedToDuplicate}]
		[{isys name="save" type="f_button" id="popup-duplicate-save-button" icon="`$dir_images`axialis/basic/symbol-ok.svg" p_strValue="LC__NAVIGATION__NAVBAR__DUPLICATE"}]
        [{/if}]
		[{isys name="C__UNIVERSAL__BUTTON_CANCEL" type="f_button" icon="`$dir_images`axialis/basic/symbol-cancel.svg" p_strValue="LC__UNIVERSAL__BUTTON_CANCEL" p_strClass="popup-closer"}]
	</div>

	<input type="hidden" name="objects" id="objects" value=""/>
	<input type="hidden" name="duplicate" id="duplicate" value="1"/>
</div>

<style type="text/css">
    .category-checkbox-list {
        min-height: 25px;
        overflow:auto;
    }

    .category-checkbox-list label {
        width: 160px;
        height: auto;
        line-height: initial;
    }

    .category-checkbox-list label input,
    .category-checkbox-list label span {
        display: block;
    }

    .category-checkbox-list label span {
        width: 135px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script language="JavaScript" type="text/javascript">
    (function () {
        'use strict';

        const $popup = $('popup-duplicate');
        const $duplicateButton = $('popup-duplicate-save-button');
        const $cancelButton = $('C__UNIVERSAL__BUTTON_CANCEL');
        let formSubmitionHandler = null;
        let afterDuplication = false;

        $popup.select('.popup-closer').invoke('on', 'click', function () {
            $('duplicate').setValue('0');

            // Check whether handler is registered
            if (formSubmitionHandler) {
                // Unregister it to prevent unwanted side effects
                formSubmitionHandler.stop();
            }

            if (!afterDuplication) {
                popup_close();
                return;
            }

            if (afterDuplication === true) {
                window.location.reload(true);
                return;
            }

            window.location.search = afterDuplication;
        });

        $duplicateButton.on('click', function () {
            $cancelButton.disable();
            $duplicateButton
                .disable()
                .down('img').addClassName('animation-rotate').writeAttribute('src', '[{$dir_images}]axialis/user-interface/loading.svg')
                .next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

            $('navMode').setValue('[{$smarty.const.C__NAVMODE__SAVE}]');

            replace_listSelection();

            new Ajax.Request(window.location.href, {
                method:     'post',
                parameters: $('isys_form').serialize(true),
                onComplete: function (xhr) {
                    const json = xhr.responseJSON;

                    if (is_json_response(xhr) && json.success) {
                        // Set the Notify messages "sticky" because we'll redirect anyhow...
                        idoit.Notify.success('[{isys type="lang" ident="LC__INFOBOX__OBJECT_WAS_DUPLICATED"}]', {sticky: true});

                        afterDuplication = json.data.redirect ? json.data.redirect : true;

                        if (Array.isArray(json.data.validationErrors) && json.data.validationErrors.length > 0) {
                            idoit.Notify.warning(json.data.validationErrors.join('<br />'), { header: '[{isys type="lang" ident="LC__SETTINGS__CMDB__VALIDATION"}]', sticky: true });

                            $cancelButton.enable().down('span').update('[{isys type="lang" ident="LC__POPUP__CLOSE"}]');
                            $duplicateButton
                                .down('img').removeClassName('animation-rotate').writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-ok.svg')
                                .next('span').update('[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DUPLICATE"}]');
                        } else {
                            if (json.data.redirect) {
                                // Simply replace the "?objID=123&..." part.
                                window.location.search = json.data.redirect;
                            } else {
                                window.location.reload(true);
                            }
                        }
                    } else {
                        idoit.Notify.error(json.message || xhr.responseText, {sticky: true});

                        $cancelButton.enable();
                        $duplicateButton
                            .enable()
                            .down('img').removeClassName('animation-rotate').writeAttribute('src', '[{$dir_images}]axialis/basic/symbol-ok.svg')
                            .next('span').update('[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DUPLICATE"}]');
                    }
                }
            });
        });

        $$('input.select_all_categories').invoke('on', 'click', function ($ele) {
            var $chkEle = $ele.findElement();
            var $select_all = $chkEle.checked;
            $chkEle.up('td').select('.categories').each(function ($el) {
                $el.checked = $select_all;
            });
        });

        [{if $smarty.get.objID}]
	        $('objects').setValue('[{$smarty.get.objID|strip_tags|escape}]');
	        $('object_title').setValue('[{$smarty.get.objTitle|strip_tags|escape}]');
        [{/if}]

        /**
         * Prevent form submit on enter keypress
         * in duplication context.
         *
         * @see ID-5516
         */
        formSubmitionHandler = $('isys_form').on('submit', function (evt) {
            // Trigger click on `duplicate` button
            $duplicateButton.simulate('click');

            // Prevent form submit
            evt.preventDefault();
        });
    })();
</script>
