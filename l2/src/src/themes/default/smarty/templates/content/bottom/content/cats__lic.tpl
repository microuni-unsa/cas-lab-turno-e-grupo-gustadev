<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_TYPE' ident="LC__CMDB__CATS__LICENCE_TYPE"}]</td>
        <td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATS__LICENCE_TYPE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_AMOUNT' ident="LC__CMDB__CATS__LICENCE_AMOUNT"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATS__LICENCE_AMOUNT"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_KEY' ident="LC__CMDB__CATS__LICENCE_KEY"}]</td>
        <td class="value">[{isys type="f_textarea" name="C__CATS__LICENCE_KEY"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_SERIAL' ident="LC__CMDB__CATS__LICENCE_SERIAL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATS__LICENCE_SERIAL"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_START__VIEW' ident="LC__CMDB__CATS__LICENCE_START"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATS__LICENCE_START" p_strPopupType="calendar"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_EXPIRE__VIEW' ident="LC__CMDB__CATS__LICENCE_EXPIRE"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATS__LICENCE_EXPIRE" p_strPopupType="calendar"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATS__LICENCE_COST' ident="LC__CMDB__CATS__LICENCE_UNIT_PRICE"}]</td>
        <td class="value">[{isys type="f_money_number" name="C__CATS__LICENCE_COST"}]</td>
    </tr>
</table>

<div class="border-top border-bottom mt20 mb20" style="position:relative; overflow: hidden; min-height: 150px; max-height: 500px;">
    <!-- @see ID-8424  Implement a table component -->
    [{$objectTable}]
</div>

<script>
	(function () {
		"use strict";

		var licence_type = $('C__CATS__LICENCE_TYPE'),
			licence_amount = $('C__CATS__LICENCE_AMOUNT');

		var check_amount = function () {
			if (licence_type.getValue() == '[{isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__VOLUME_LICENCE}]' ||
                licence_type.getValue() == '[{isys_cmdb_dao_category_s_lic::C__CATS__LICENCE_TYPE__CPU_CORE_BASED_LICENCE}]'
            ) {
				licence_amount.readOnly = false;
			} else {
				licence_amount.setValue(1);
				licence_amount.readOnly = true;
			}

			// For triggering the validator.
			licence_amount.simulate('change');
		};

		if (licence_type) {
			licence_type.on('change', check_amount);
			check_amount();
		}
	}());
</script>