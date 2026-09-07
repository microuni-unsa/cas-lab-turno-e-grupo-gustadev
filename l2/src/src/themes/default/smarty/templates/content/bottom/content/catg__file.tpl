<table class="contentTable">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_OBJ_FILE' ident="LC__CMDB__CATG__FILE_OBJ_FILE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="browser_file" name="C__CATG__FILE_OBJ_FILE"}]</td>
    </tr>
    [{if $file_uploaded}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__FILE_NAME' ident="LC__CMDB__CATS__FILE_NAME"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATG__FILE_NAME"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='lang' ident="LC__CMDB__CATS__FILE_DOWNLOAD"}]</td>
        <td class="value">
	        [{if $allowedToView}]
	        <a class="btn ml20" href="[{$downloadUrl}]" target="_blank">
		        <img src="[{$dir_images}]axialis/basic/symbol-download.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
	        </a>
	        [{else}]
            <p class="p5 ml20 box-yellow">
                <img src="[{$dir_images}]axialis/basic/warning.svg" class="mr5 vam" /><span>[{isys type="lang" ident="LC__CMDB__CATG__FILE__DOWNLOAD_MISSING_VIEW_RIGHT"}]</span>
            </p>
	        [{/if}]
        </td>
    </tr>
    <tr>
        <td class="category-spacer" colspan="2"><hr /></td>
    </tr>
    [{/if}]
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATG__FILE__FILE_LINK" name="C__CATG__FILE_LINK"}]</td>
        <td class="value">[{isys type="f_link" name="C__CATG__FILE_LINK" p_maxLength=512}]</td>
    </tr>
</table>
