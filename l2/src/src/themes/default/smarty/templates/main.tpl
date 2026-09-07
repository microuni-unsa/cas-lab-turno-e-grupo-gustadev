[{isys_group name="tom"}]
<!doctype html>
<html lang="[{$activeLanguage.short|lower|default:"en"}]" class="[{$htmlClassName}]">

[{strip}]
[{include file="head.tpl"}]

[{include file="content/form.tpl"}]

<div id="wrapper">
    <div id="nag" style="display:none;"></div>

    <div id="modal-container"></div>

    <div id="overlay" style="display:none;z-index:1000;"></div>
    <div id="popup" class="popup shadow-lg slideDown" style="display:none;z-index:1200"></div>
    <div id="popup_commentary" class="popup shadow-lg slideDown" style="display:none;z-index:1100;"></div>

    [{if is_object($session) && $session->is_logged_in() && is_object($tfa) && $tfa->needsVerification()}]
        [{include file="../../../../../src/classes/modules/user_settings/templates/integration/tfa-verification.tpl"}]
    [{elseif is_object($session) && $session->is_logged_in()}]
        <div id="top">
            [{include file="top/mainMenu.tpl"}]
        </div>

        [{if $bannerTemplate && file_exists($bannerTemplate)}]
        [{include file=$bannerTemplate}]
        [{/if}]

        <div id="content">
            <div id="mydoitArea" class="hide shadow-lg"></div>
            <div id="menuTreeOn" style="width:[{$menu_width}]px;">
                [{include file=$index_includes.leftcontent|default:"content/leftContent.tpl"}]
            </div>

            <div id="draggableBar" class="draggableBar"></div>
            <div id="contentArea" class="display-container" data-default-width="[{$defaultWidth}]" style="left:[{$menu_width}]px;">
                [{include file="content/contentArea.tpl"}]
            </div>

        </div>

        [{strip}]
            <script type="text/javascript">
                [{include file="main-inline.js"}]

                /* Load additional inline scripts */
                Event.observe(window, 'load', function () {
                    /* This "inline" JS can come from anywhere (categories, modules, API, ...) */
                    [{if is_array($additionalInlineJS)}]
                        [{foreach $additionalInlineJS as $jsSnippet}]
                            [{$jsSnippet}]
                        [{/foreach}]
                    [{elseif is_string($additionalInlineJS)}]
                        [{$additionalInlineJS}]
                    [{/if}]
                });
            </script>
        [{/strip}]

    [{else}]
        [{include file="login.tpl"}]
    [{/if}]

</div>
</form>

[{if !empty($g_error)}]
	[{if isys_tenantsettings::get('system.devmode')}]
	<script type="text/javascript">
		document.observe('dom:loaded', function() {
			idoit.Notify.message('Usage of "$g_error" detected. Please use <strong>isys_application::instance() ->container["notify"] ->error("...");</strong> instead.', {sticky: true})
		});
	</script>
	[{/if}]

	[{include file="exception.tpl"}]
[{/if}]

[{if $trialInfo}]
	<li class="bold red">
		[{$trialInfo.message}]
	</li>

	<div id="freeTrialBadge">
		<img src="[{$config.www_dir}]src/classes/modules/pro/images/free-trial.png" onclick="alert('[{$trialInfo.title}]: [{$trialInfo.message}]');" alt="" />
	</div>
[{/if}]

	[{if $notification}]
	<script type="text/javascript">
		(function(){
			'use strict';

			try {
				var notifications = '[{$notification|json_encode|escape:"javascript"}]'.evalJSON(), i;

				document.observe('dom:loaded', function() {
					for (i in notifications) {
						if (notifications.hasOwnProperty(i)) {
							idoit.Notify.renderNotification(notifications[i]);
						}
					}
				});
			} catch (e) {
				// ...
			}
		})();
	</script>
	[{/if}]
</body>
</html>
[{/strip}]
[{/isys_group}]
