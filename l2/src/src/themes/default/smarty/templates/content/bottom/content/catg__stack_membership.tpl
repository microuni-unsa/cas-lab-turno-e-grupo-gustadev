<div id="catg-stack-view" class="p20">
	[{if $is_stacked}]
		[{foreach $stacks as $stack}]
		<div class="mb20">
			<h3 class="p10 bg-neutral-200">[{isys type="lang" ident="LC__CATG__STACK_MEMBERSHIP__STACK"}] <span class="ml20">[{$stack.quickinfo}]</span></h3>

			<ul>
				[{foreach $stack.members as $member}]
				<li>[{$member}]</li>
				[{/foreach}]
			</ul>
		</div>
		[{/foreach}]
	[{else}]
		<div class="p5 box-blue display-flex align-items-center">
            <img src="[{$dir_images}]axialis/basic/button-info.svg" alt="" class="mr5" /><span>[{isys type="lang" ident="LC__CATG__STACK_MEMBERSHIP__NO_MEMBERSHIP"}]</span>
        </div>
	[{/if}]
</div>

<style>
	#catg-stack-view ul {
        margin: 0;
		padding: 0;
        list-style: none;
	}
	#catg-stack-view ul li {
        margin: 0;
		padding: 10px;
		border-bottom: 1px solid #888;
	}

	#catg-stack-view ul li:last-child {
		border-bottom: none;
	}
</style>
