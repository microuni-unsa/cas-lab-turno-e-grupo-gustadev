[{if $bShowMenuTreeButtons}]
<div class="p20 pb15">
    <select id="sidebar-changer" class="input input-block">
        [{foreach $sidebarViewTypes as $type => $viewType}]
        <option value="[{$type}]" [{if $viewType.selected}]selected[{/if}]>[{isys type="lang" ident=$viewType.title}]</option>
        [{/foreach}]
    </select>
</div>

<script>
    (function(){
        const $sidebarSwitcher = $('sidebar-changer');

        $sidebarSwitcher.on('change', function () {
            if ($sidebarSwitcher.getValue() === 'object') {
                [{$sidebarViewTypes.object.js}];
            } else {
                [{$sidebarViewTypes.location.js}];
            }
        });
    })()
</script>
[{/if}]

[{if $smarty.get.objID && $menuTreeStickyLinks}]
	<div id="treeTop">
		[{foreach $menuTreeStickyLinks as $identifier => $item}]
		<a class="btn btn-secondary" href="[{$item.link}]" title="[{$item.title}]" data-tooltip="1"><img src="[{$item.icon}]" alt="[{$item.title}]" /></a>
		[{/foreach}]
	</div>
[{/if}]
