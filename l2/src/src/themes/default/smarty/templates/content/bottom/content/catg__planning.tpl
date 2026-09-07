<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PLANNING__STATUS' ident="LC__UNIVERSAL__CMDB_STATUS"}]</td>
        <td class="value">
            <div class="ml20 input-group input-size-small">
	            [{isys type="f_dialog" default="n/a" p_bDbFieldNN="1" name="C__CATG__PLANNING__STATUS" p_strTable="isys_cmdb_status" disableInputGroup=true p_bInfoIconSpacer="0"}]
                [{if isys_glob_is_edit_mode()}]
                <div class="input-group-addon">
	                <div class="colorpicker-pill" id="cmdb_status_color" style="background-color:[{$status_color}];"></div>
                </div>
                [{else}]
                <div class="cmdb-marker vam" id="cmdb_status_color" style="background-color:[{$status_color}];"></div>
                [{/if}]
            </div>
            <br class="cb"/>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__PLANNING__START__VIEW' ident="LC__UNIVERSAL__VALIDITY"}]</td>
        <td class="value">
	        [{if isys_glob_is_edit_mode()}]
		        <div class="input-group input-size-medium ml20">
		            [{isys type="f_popup" name="C__CATG__PLANNING__START" p_strPopupType="calendar" disableInputGroup=true p_bInfoIconSpacer="0"}]
		            <div class="input-group-addon input-group-addon-unstyled">[{isys type="lang" ident="LC__UNIVERSAL_TO"}]</div>
		            [{isys type="f_popup" name="C__CATG__PLANNING__END" p_strPopupType="calendar" disableInputGroup=true p_bInfoIconSpacer="0"}]
		        </div>
	        [{else}]
	            [{isys type="f_popup" name="C__CATG__PLANNING__START" p_strPopupType="calendar"}]
	            <span class="ml5 mr5">[{isys type="lang" ident="LC__UNIVERSAL_TO"}]</span>
	            [{isys type="f_popup" name="C__CATG__PLANNING__END" p_strPopupType="calendar" p_bInfoIconSpacer="0"}]
	        [{/if}]
        </td>
    </tr>
</table>

<script type="text/javascript">
    (function () {
        'use strict';

        var $cmdbStatus        = $('C__CATG__PLANNING__STATUS'),
            cmdb_status_colors = JSON.parse('[{$status_colors}]');

        if ($cmdbStatus) {
            $cmdbStatus.on('change', function () {
                const cmdbStatusValue = $cmdbStatus.getValue();

                if (cmdb_status_colors.hasOwnProperty(cmdbStatusValue)) {
                    $('cmdb_status_color').setStyle({ backgroundColor: cmdb_status_colors[cmdbStatusValue] });
                }
            });

            $cmdbStatus.simulate('change');
        }
    }());
</script>
