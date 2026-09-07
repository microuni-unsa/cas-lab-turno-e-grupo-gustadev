<div id="popup-report-duplicate">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_DUPLICATE"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content">
		<input type="hidden" name="report_id" value="[{$report_id}]">

		<table class="contentTable mt10">
			<tr>
				<td class="key">[{isys type="f_label" name="title" ident="LC__REPORT__FORM__TITLE"}]</td>
				<td class="value">[{isys type="f_text" name="title" id="title" p_strClass="input-small" p_strValue=$report_title}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="report_category" ident="LC_UNIVERSAL__CATEGORY"}]</td>
				<td class="value">[{isys type="f_dialog" name="category_selection" p_bDbFieldNN=1 p_arData=$category_selection p_strSelectedID=$report_category id="category_selection" p_strClass="input-small"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="f_label" name="description" ident="LC__REPORT__FORM__DESCRIPTION"}]</td>
				<td class="value">[{isys type="f_textarea" name="description" p_nRows="5" p_strClass="input-small" p_strValue=$report_description}]</td>
			</tr>
		</table>
	</div>

	<div class="popup-footer-ng">
		[{if !$error}]
			<button type="button" class="btn mr5" id="duplicate_button">
				<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" /><span>[{isys type="lang" ident="LC__NAVIGATION__NAVBAR__DUPLICATE"}]</span>
			</button>
		[{/if}]

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-report-duplicate'),
			$duplicate_button = $('duplicate_button');

        $popup.on('keydown', '#title,#category_selection', function (ev) {
            if (ev.key.toLowerCase() === 'enter') {
                ev.preventDefault();
            }
        })

		if ($duplicate_button) {
			$duplicate_button.on('click', function () {
				popup_close();
				$('navMode').setValue('[{$smarty.const.C__NAVMODE__DUPLICATE}]');
				$('isys_form').submit();
			});
		}

		$$('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		[{if $force_close}]
		popup_close();
		[{/if}]
	})();
</script>

<style type="text/css">
	#popup-report-duplicate {
		height: 100%;
	}

	#popup-report-duplicate .contentTable td.key {
		width: 100px;
	}
</style>
