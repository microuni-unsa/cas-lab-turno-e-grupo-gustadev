<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_LOGIN_DISABLED" name="C__CONTACT__PERSON__DISABLED_LOGIN"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CONTACT__PERSON__DISABLED_LOGIN" p_bDbFieldNN="1" tab="70"}]
    </tr>
    [{if $logLastLogin}]
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_LAST_LOGIN" name="C__CONTACT__PERSON_LAST_LOGIN"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CONTACT__PERSON_LAST_LOGIN" p_bReadonly=true p_strPopupType="calendar"}]</td>
    </tr>
    [{else}]
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CONTACT__PERSON_LAST_LOGIN"}]</td>
        <td class="value pl20">
            <div class="display-flex align-items-center box-blue p5 w50">
                <img src="[{$dir_images}]/axialis/basic/button-info.svg" alt="" class="mr5" />
                <p>[{isys type="lang" ident="LC__CONTACT__PERSON_LAST_LOGIN__INFO"}]</p>
            </div>
        </td>
    </tr>
    [{/if}]
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_USER_NAME" name="C__CONTACT__PERSON_USER_NAME"}]</td>
		<td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_USER_NAME"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_PASSWORD" name="C__CONTACT__PERSON_PASSWORD"}]</td>
		<td class="value">[{isys type="f_password" name="C__CONTACT__PERSON_PASSWORD"}]</td>
	</tr>
    [{if isys_glob_is_edit_mode()}]
    <tr>
        <td class="key"></td>
        <td class="value">[{isys type="f_popup" p_strPopupType="change_password" name="C__CONTACT__CHANGE_PASSWORD_POPUP"}]</td>
    </tr>
    [{/if}]
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_PASSWORD_RESET_EMAIL" name="C__CONTACT__PERSON_PASSWORD_RESET_EMAIL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PASSWORD_RESET_EMAIL"}]</td>
    </tr>
</table>
