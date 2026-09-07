<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CATG__OVERVIEW__DATE_FORMAT" name="C__CATG__OVERVIEW__DATE_FORMAT"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__DATE_FORMAT"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CATG__OVERVIEW__NUMERIC_FORMAT" name="C__CATG__OVERVIEW__NUMERIC_FORMAT"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__CATG__OVERVIEW__NUMERIC_FORMAT"}]</td>
    </tr>
    <tr>
        <td class="key vat">[{isys type="f_label" ident="LC__CATG__OVERVIEW__MONETARY_FORMAT" name="C__CATG__OVERVIEW__MONETARY_FORMAT"}]</td>
        <td class="value">
            [{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__OVERVIEW__MONETARY_FORMAT"}]
            [{if isys_glob_is_edit_mode()}]
            <div class="cb pl20 pt10 text-blue">
                <img src="[{$dir_images}]axialis/basic/button-help.svg" class="vam mr5" alt="" />
                <span class="vam">[{isys type="lang" ident="LC__CATG__OVERVIEW__MONETARY_FORMAT_DESCRIPTION"}]</span>
            </div>
            [{/if}]
        </td>
    </tr>
</table>
