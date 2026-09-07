[{strip}]
<div id="navBar">
    [{if $lastBreadcrumbItem || $content_title || $categoryTitle}]
    <h2>[{$lastBreadcrumbItem|default:$categoryTitle|default:$content_title}]</h2>
    [{/if}]

	[{if $navbar_buttons}]
		[{foreach $navbar_buttons as $key => $button}]
			[{if $key == 'C__NAVBAR_BUTTON__NEW' || $key == 'C__NAVBAR_BUTTON__PRINT'}]
				[{$button}]
			[{/if}]
		[{/foreach}]
	[{/if}]

    <!-- TODO  Refactor this to new format -->
    <a class="btn btn-secondary" onclick="expandAllLogbookChanges();">
        <img src="[{$dir_images}]axialis/database/data.svg" alt="" />
        <span>[{isys type="lang" ident="LC__LOGBOOK__EXPAND_ALL_CHANGES"}]</span>
    </a>

    <div style="flex-grow: 1;"></div>

	<a id="navbar_item_8" title="[{isys type="lang" ident="LC__UNIVERSAL__LINK_TO_THIS_PAGE"}]" class="btn btn-secondary" href="[{$current_link}]">
        <img src="[{$dir_images}]axialis/documents-folders/link.svg" alt="" />
	</a>

    <div id="cSpanRecFilter" [{if $hideRecStatus}]class="hide"[{/if}]>
        [{include file="content/top/list_paging.tpl"}]

        [{if ($list_display)}]
            [{isys type="f_dialog"
                p_strClass="input input-mini"
                id="cRecStatus"
                name="cRecStatus"
                p_bEditMode="1"
                p_strStyle="width:100px;"
                p_onClick=""
                p_bDbFieldNN="1"
                p_onChange="form_submit();"}]
        [{/if}]
    </div>
</div>
[{/strip}]

<script type="text/javascript">
    $$('#cSpanRecFilter .pager-filter').invoke('hide');
</script>
