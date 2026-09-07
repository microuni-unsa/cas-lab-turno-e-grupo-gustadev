<div class="popup-header-ng">
    <h1>[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_PREVIEW"}]</h1>
    <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
        <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
    </button>
</div>

<div class="popup-content" style="overflow-x: auto;">
    [{if $groupingRelatedSortingHint}]
        <div class="m5 p10 box-blue">
            <img src="[{$dir_images}]icons/silk/information.png" class="vam mr5" />
            [{$groupingRelatedSortingHint}]
        </div>
    [{/if}]
	<div id="mainList">
		[{if $message}]
			<div class="m5 p5 [{$message_class}]">[{$message}]</div>
		[{/if}]
	</div>
</div>

<div class="popup-footer-ng">
    <button type="button" class="btn popup-closer">
        <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
    </button>
</div>

<script type="text/javascript">
	[{if $show_preview}]
	new Lists.ReportList('mainList', {
		data: [{$l_json_data}],
		draggable: false,
		checkboxes: false,
		tr_click: false,
        unsortedColumn: [{$unsortedColumns}]
	});
	[{/if}]

    $$('.popup-closer').invoke('on', 'click', function () {
        popup_close();
    });
</script>
