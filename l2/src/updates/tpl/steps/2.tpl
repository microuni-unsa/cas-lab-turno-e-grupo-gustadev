<script type="text/javascript">
    'use strict'
	function check(el) {
		if (el.checked=="") el.checked="checked";
	}

    /**
     * Check if user tries to update an
     * OPEN instance from source version < 29
     * to target version >= 29 to inform him
     * about the feature limitations.
     *
     * @param successHandler
     */
    function open29NoticeHandler(successHandler) {
        const thresholdRevision = 202429000;
        const currentRevision = [{$g_info.revision}];
        const selectedRevision = $$('input[name=dir]:checked').first();
        const targetRevision = selectedRevision && selectedRevision.getAttribute('data-revision');

        const isSourceBefore29 = currentRevision < thresholdRevision;
        const isTargetAfter29 = !targetRevision || targetRevision >= thresholdRevision;
        const isOpen = '[{$g_product_info.type}]' === 'OPEN';

        if (isOpen && isSourceBefore29 && isTargetAfter29) {
            $('overlay').show();
            $('modal').show();

            let counter = 10;
            const counterInterval = setInterval(() => {
                $('counter').innerHTML = `Please wait 00:${(--counter).toString().padStart(2, '0')} seconds...`
                if (counter <= 0) {
                    clearInterval(counterInterval);
                    $('confirm').enable();
                    $('confirm').removeClassName('disabled');
                    $('counter').remove();
                }
            }, 1000);
            return;
        }
        successHandler();
    }
</script>

[{include '../partials/open-29-notice.tpl'}]

<h2>Available Updates</h2>

[{if !$licence_error}]
	<h3>New Versions</h3>

	<p>New version can be retrieved via <a href="[{$site_url}]">[{$site_url}]</a>.</p>

	[{if is_array($g_update) && count($g_update)}]
		<h3>There is a newer version available!</h3>

        <input type="hidden" id="dl_file" name="dl_file" value="" />

        <table class="listing" cellpadding="2" cellspacing="0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Release / Revision</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                [{foreach $g_update as $item}]
                    <tr class="[{cycle values="even,odd"}]">
                        <td>[{$item.title}]</td>
                        <td>Date: [{$item.release}] (rev [{$item.revision}])</td>
                        <td>
                            <input
                                type="button"
                                class="button slim"
                                name="btn_download"
                                value="Download"
                                onClick="$('dl_file').setValue('[{$item.revision}]'); $('isys_form').submit();" />
                        </td>
                    </tr>
                [{/foreach}]
            </tbody>
        </table>
	[{else}]
		[{if $g_downloaded}]
			Download successfull.
		[{else}]
			[{if $g_update_message}]
				<p class="bold message [{$g_update_message_class}]">[{$g_update_message}]</p>
			[{else}]
				<input type="hidden" id="check_update" name="check_update" value="false" />
				<input type="button" class="button" name="btn_check"  value="Check for a new version" onClick="$('check_update').setValue('true'); $('isys_form').submit();" />
			[{/if}]
		[{/if}]
	[{/if}]

	<h3>You can update to the following already downloaded versions:</h3>
	<table id="update-table" cellpadding="2" cellspacing="0" width="100%" class="listing" style="margin-top:15px;">
		<colgroup>
			<col width="100" />
		</colgroup>
		<thead>
			<tr>
				<th>Use</th>
				<th>Name</th>
				<th>Version</th>
				<th>Requirements</th>
			</tr>
		</thead>
		<tbody>
			[{if is_array($updates) && count($updates)>0}]
				[{foreach $updates as $update}]
				[{counter print=false assign="i"}]
				[{if $update.changelog != "n/a"}]
				<div id="changelog_[{$update.revision}]" class="changelog" style="display:none;">
					<div class="innerchangelog">
						<div class="header">
							<div class="header_left">Changelog</div>
							<div class="header_right">
								<a class="link" onclick="new Effect.Fade('changelog_[{$update.revision}]', {duration:0.2});">close</a>
							</div>
							<div style="clear:both"></div>
						</div>
						<div class="bottom"><pre>[{$update.changelog}]</pre></div>
					</div>
				</div>
				[{/if}]

				<tr class="[{cycle values="even,odd"}]">
					<td title="Attention! You will need to update (or remove) the following incompatible add-ons to the newest version in order to update i-doit: [{$update.incompatible|escape}]">
                        <div style="display: flex; align-items: center;">
                            <input
                                type="radio"
                                id="dir_[{$i}]"
                                name="dir"
                                value="[{$update.directory}]"
                                [{if $update.disabled}]disabled="disabled"[{/if}]
                                data-revision="[{$update.revision}]"
                                style="margin:0;"
                            />

                            [{if $update.incompatible}]
                            <img
                                src="[{$dir_images}]axialis/basic/warning.svg"
                                alt="Attention! You will need to update (or remove) the following incompatible add-ons to the newest version in order to update i-doit: [{$update.incompatible|escape}]"
                                style="margin-left:5px" />
                            [{/if}]
                        </div>
					</td>
					<td>
                        <span [{if $update.revision eq $g_info.revision}]style="font-weight:bold;"[{/if}]>
                            [{$update.title}]
                            [{if $update.changelog != "n/a"}] (<a class="link" onclick="new Effect.Appear('changelog_[{$update.revision}]', {duration:0.3});">see changelog</a>)[{/if}]
                        </span>
					</td>
					<td>
						[{if !empty($update.revision)}]
							[{$update.version}] (rev [{$update.revision}])
						[{/if}]
						</td>
					<td>
						[{if !empty($update.revision)}]
							[{if $update.revision < $update.requirement.revision}]
								<strong style="color:red;">
									[{$update.requirement.version}]
									[{if $update.requirement.revision}] rev [{$update.requirement.revision}][{/if}]
								</strong>
							[{else}]
								[{$update.requirement.version}]
								[{if $update.requirement.revision}] rev [{$update.requirement.revision}][{/if}]
							[{/if}]
						[{/if}]
					</td>
				</tr>

				[{/foreach}]
			[{else}]
				<tr>
					<td colspan="2"><span>There don't seem to be any versions to update to.</span></td>
				</tr>
			[{/if}]
		</tbody>
	</table>

	<p>Select a version and click "Next >>" to go to the next step.</p>
	<span>Your current version is: <strong>[{$g_info.version|default:"<= 0.9"}] (rev [{$g_info.revision|default:"<= 2500"}])</strong></span>

<script>
    (function(){
        const $updateTable = $('update-table');

        if ($updateTable) {
            const $lastActiveRadio = $updateTable.down('[name="dir"]:not(:disabled):last');

            if ($lastActiveRadio) {
                $lastActiveRadio.setValue(1);
            }
        }
    })();
</script>

[{else}]
	<br />
	<div class="exception p10">
		[{$licence_error}]
	</div>
[{/if}]
