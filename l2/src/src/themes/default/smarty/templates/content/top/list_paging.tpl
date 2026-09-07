[{if is_array($pages) || isset($smarty.post.filter)}]
	<div class="display-flex align-items-center">
		[{if is_array($pages) && count($pages) > 0}]
        [{isys
            name="current_page"
            type="f_dialog"
            p_bSort=false
            p_bDbFieldNN=true
            disableInputGroup=true
            p_bInfoIconSpacer=0
            p_arData=$pages
            p_strSelectedID=$page_start
            p_strStyle="width: 80px; margin-right: 16px"
            p_onChange="change_page(\$('current_page').value, false, 'main_content', 'post');"}]
		[{/if}]

        <div class="pager-results mr20">
            [{if $page_current != "" && $page_max != "" && $page_info != ""}]
                <img src="[{$dir_images}]/axialis/basic/pages-stack.svg" class="mr5" alt="" />
                <span>[{$page_info}]</span>
            [{/if}]

            [{if $page_results > 0}]
                <img src="[{$dir_images}]/axialis/documents-folders/document.svg" class="ml10 mr5" alt="" />
                <span>[{$page_results}]</span>
            [{/if}]
        </div>

        <!-- Not sure if this is used anywhere... -->
		<div class="pager-filter">
			<input name="filter" type="search" class="input input-mini" placeholder="[{isys type="lang" ident="LC_UNIVERSAL__FILTER_LIST"}]" id="filter" incremental="incremental" value="[{$smarty.post.filter}]" />
		</div>
	</div>

	<script type="text/javascript">
		var $filter = $('filter'),
			$navPageStart = $('navPageStart');

		$filter.on('search', function() {
			$navPageStart.setValue(0);
			form_submit();
		});

		$filter.on('keypress', function (ev) {
			if ((ev.which && ev.which == Event.KEY_RETURN) || (ev.keyCode && ev.keyCode == Event.KEY_RETURN)) {
				$navPageStart.setValue(0);
				form_submit();
				ev.preventDefault();

				return false;
			}

			return true;
		});

		[{if $smarty.post.filter}]$filter.focus();$filter.setValue($filter.value);[{/if}]
	</script>
[{/if}]
