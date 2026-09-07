[{isys_group name="tom.popup.change-password"}]
<div id="change-password-popup">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__LOGIN__PASSWORD_CHANGE"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content">
        <table class="contentTable">
            <tr>
                <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_PASSWORD" name="NEW_PASSWORD"}]</td>
                <td class="value">[{isys type="f_password" name="NEW_PASSWORD"}]</td>
            </tr>
            <tr>
                <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_PASSWORD" name="NEW_PASSWORD_SECOND"}]</td>
                <td class="value">[{isys type="f_password" name="NEW_PASSWORD_CONFIRM"}]</td>
            </tr>
        </table>
	</div>

	<div class="popup-footer-ng">
		<button type="button" class="btn save-button mr5">
			<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" /><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>
[{/isys_group}]

<style>
    #change-password-popup td.key {
        width: 140px;
    }
</style>

<script type="text/javascript">
	(function () {
		"use strict";

        const $popup = $('change-password-popup');
		const $targetField = $('[{$targetField}]');
		const $dataField = $('[{$dataField}]');
		const $clearTriggerButton = $('[{$clearTriggerButton}]');

        if (!$targetField || !$dataField || !$clearTriggerButton) {
            idoit.Notify.error('Some elements are not ready!');
        }

        const passwordMinimumLength = +'[{$passwordMinimumLength}]'

        $popup.on('click', '.save-button', function () {
            const newPassword = $F('NEW_PASSWORD').trim();
            const newPasswordConfirm = $F('NEW_PASSWORD_CONFIRM').trim();

            if (newPassword !== newPasswordConfirm) {
                idoit.Notify.error('[{isys type="lang" ident="LC__LOGIN__PASSWORDS_DONT_MATCH"}]', { life: 5 });
                return;
            }

            if (newPassword.length < passwordMinimumLength) {
                idoit.Notify.error('[{isys type="lang" ident="LC__LOGIN__SAVE_ERROR"}]'.replace('%d', passwordMinimumLength), { life: 5 });
                return;
            }

            $targetField.setValue(newPassword);
            $dataField.setValue('set');

            [{if $showClearTriggerButton}]
            $clearTriggerButton.removeClassName('hide');
            [{/if}]

            popup_close();
        });

        $popup.on('click', '.popup-closer', () => popup_close());
	}());
</script>
