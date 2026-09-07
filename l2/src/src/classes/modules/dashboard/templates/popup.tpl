<style type="text/css">
	/* We include the styling directly for smarty variables inside the styling and no cache problems. */
	[{include file=$css_path}]
</style>

<script type="text/javascript">
	// Set this to false in your config-template, if you don't want to use the automatic save.
	window.automatic_save = true;
</script>

<div id="widget-popup">
    <div class="popup-header-ng">
        <h1>[{$title}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div id="widget-popup-config-container" class="popup-content">
		[{$content}]
	</div>

	[{isys type="f_text" id="widget-popup-config-changed" p_bInfoIconSpacer=0 disableInputGroup=true p_bInvisible=true p_strValue=0}]
	[{isys type="f_text" id="widget-popup-config-hidden" p_bInfoIconSpacer=0 disableInputGroup=true p_bInvisible=true}]

	<div class="popup-footer-ng">
		<button id="widget-popup-accept" type="button" class="btn mr5">
			<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" /><span>[{isys type="lang" ident="LC__WIDGET__CONFIG__ACCEPT"}]</span>
		</button>
		<button id="widget-popup-abort" type="button" class="btn popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" /><span>[{isys type="lang" ident="LC__WIDGET__CONFIG__ABORT"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function() {
		'use strict';

		var $popup = $('widget-popup'),
			$popup_content = $popup.down('.popup-content');

		$popup.select('.popup-closer').invoke('on', 'click', function() {
			popup_close($('widget-container-popup'));
		});
	})();


	$('widget-popup-config-container').on('change', 'input', function () {
		$('widget-popup-config-changed').setValue('1');
	});

	if (window.automatic_save) {
		$('widget-popup-accept').on('click', function(ev) {
			ev.findElement('button')
				.down('img').writeAttribute('src', '[{$dir_images}]ajax-loading.gif')
				.next('span').update('[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]');

			if ($F('widget-popup-config-changed') == 1) {
				window.dashboard.save_config_and_reload_widget('[{$ajax_url}]', {id:'[{$config_id}]', unique_id:'[{$unique_id}]', config:$F('widget-popup-config-hidden')});
			}

            popup_close($('widget-container-popup'));
		});
	}
</script>
