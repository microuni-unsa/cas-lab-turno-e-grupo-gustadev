<div class="quicklaunch">
	[{if count($function_list) > 0}]
	<div>
		<div class="mb10 display-flex align-items-center">
            <img src="[{$dir_images}]axialis/basic/windows.svg" class="mr5" alt="" />
            <strong>[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_FUNCTIONS"}]</strong>
        </div>

        [{foreach $function_list as $url => $name}]
        <a class="btn btn-block mb5" href="[{$url}]">[{$name}]</a>
        [{/foreach}]
	</div>
	[{/if}]

	[{if count($configuration_list) > 0}]
	<div>
		<div class="mb10 display-flex align-items-center">
            <img src="[{$dir_images}]axialis/basic/gear.svg" class="mr5" alt="" />
            <strong>[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_CONFIGURATION"}]</strong>
        </div>

        [{foreach $configuration_list as $url => $name}]
        <a class="btn btn-block mb5" href="[{$url}]">[{$name}]</a>
        [{/foreach}]
	</div>
	[{/if}]

	[{if $allow_update}]
	<div>
		<div class="mb10 display-flex align-items-center">
            <img src="[{$dir_images}]axialis/basic/symbol-update.svg" class="mr5" alt="" />
            <strong>[{isys type="lang" ident="LC__WIDGET__QUICKLAUNCH_IDOIT_UPDATE"}]</strong>
        </div>

        <a class="btn btn-block mb5" href="./updates">i-doit Update</a>
	</div>
	[{/if}]
</div>

<style>
	.quicklaunch {
		display: flex;
	}

	.quicklaunch > div {
        width: 33%;
        padding-right: 20px;
    }

	.quicklaunch > div:last-of-type {
		padding-right: 0;
	}
</style>
