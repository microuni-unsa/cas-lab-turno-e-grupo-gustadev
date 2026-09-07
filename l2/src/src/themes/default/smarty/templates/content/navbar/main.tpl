[{strip}]
<div id="navBar">
    [{if $lastBreadcrumbItem || $content_title || $categoryTitle}]
    <h2>[{$lastBreadcrumbItem|default:$categoryTitle|default:$content_title}]</h2>
    [{/if}]

	[{if $navbar_buttons}]
		[{foreach $navbar_buttons as $button}]
			[{$button}]
		[{/foreach}]
	[{/if}]

    <div style="flex-grow: 1;"></div>

    [{foreach $navbarStickyButtons as $button}]
        [{$button}]
    [{/foreach}]

	<a id="navbar_item_8" title="[{isys type="lang" ident="LC__UNIVERSAL__LINK_TO_THIS_PAGE"}]" class="btn btn-secondary" href="[{$current_link}]" data-tooltip="1">
		<img src="[{$dir_images}]axialis/documents-folders/link.svg" alt="" />
	</a>

	[{if $list_display}]
		<div id="cSpanRecFilter" [{if $hideRecStatus}]class="hide"[{/if}]>
            [{include file="content/top/list_paging.tpl"}]

			<div style="width: 130px">
                [{isys
                    type="f_dialog"
                    p_strClass="input input-block"
                    id="cRecStatus"
                    name="cRecStatus"
                    p_bEditMode=true
                    p_onClick=""
                    p_bDbFieldNN=true
                    inputGroupMarginClass=""
                    p_onChange="\$('navMode').setValue('');remove_action_parameter(['navPageStart', 'page']);form_submit();"}]
            </div>
		</div>
	[{/if}]

	[{* This Submit-Button is used for enabling saving by pressing enter in input elements *}]
	<input type="submit" name="submit_isys_form" id="submit_isys_form" value="" style="visibility: hidden; width: 0; height: 0; position: absolute;"/>

</div>
[{/strip}]

<script type="text/javascript">
	$('isys_form').stopObserving('submit');

    $('isys_form').observe("submit", function(evt) {
        if ($('navbar_item_C__NAVMODE__SAVE')) {
            $('navbar_item_C__NAVMODE__SAVE').simulate('click');

            Event.stop(evt);
        }
    });
</script>
