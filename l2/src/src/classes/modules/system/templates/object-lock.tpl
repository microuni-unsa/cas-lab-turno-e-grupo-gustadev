<div class="m20 box-blue p10 display-flex align-items-center">
    <img src="[{$dir_images}]axialis/basic/button-info.svg" class="mr10" />
    <p>
        [{isys type="lang" ident="LC__LOCKING__DESCRIPTION" p_bHtmlEncode=false}]
    </p>
</div>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="lock" ident="LC__LOCKING__ACTIVATE"}]</td>
        <td class="value">[{isys type="f_dialog" name="lock" }]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="lock_timeout" ident="Timeout"}]</td>
        <td class="value">[{isys type="f_count" name="lock_timeout"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="lang" ident="LC__LOCKED__OBJECTS_CURRENTLY"}]</td>
        <td class="value pl20">
            [{if $g_list}]
                [{if $smarty.post.navMode == 2}]
                    <button type="button" class="btn" onclick="$('isys_form').action = $('isys_form').action +'&delete_locks'; $('isys_form').submit()">
                        <img src="[{$dir_images}]icons/silk/page_delete.png" alt=""><span>[{isys type="lang" ident="LC_UNIVERSAL__DELETE"}]</span>
                    </button>
                [{/if}]
            [{else}]
                <p class="p5 text-green"><img src="[{$dir_images}]axialis/basic/symbol-ok.svg" class="vam mr5"/><span>[{isys type="lang" ident="LC_UNIVERSAL__NONE"}]</span></p>
            [{/if}]
        </td>
    </tr>
</table>

[{if $g_list}]
<div style="position: relative; max-height: 400px;">
    [{$g_list}]
</div>
[{/if}]
