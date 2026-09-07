<div id="popup-sanzone">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__CATD__SANPOOL_DEVICES"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content pt10 px10">
		<div style="height: 235px; overflow:auto;">[{$browser}]</div>

		<p class="p5">
			<span id="selection-text-label">[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]</span>: <strong id="selection-text">[{$selFull|default:$selNoSelection}]</strong>
		</p>
	</div>

    <div class="popup-footer-ng">
        <button type="button" id="popup-sanzone-save" class="btn mr5">
            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__BUTTON_SAVE"}]</span>
        </button>
        <button type="button" class="btn popup-closer">
            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
        </button>
    </div>
</div>

<script language="JavaScript" type="text/javascript">
	(function () {
		'use strict';

		var $container = $('popup-sanzone'),
			$tree = $('ldev_browser'),
			$selection_text = $('selection-text'),
			selected_devices = [],
			selected_raids = [],
			devices = JSON.parse('[{$deviceList}]'),
			raids = JSON.parse('[{$raidList}]');

		$container.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		$tree.on('change', 'input[type="checkbox"]', function () {
			idoit.callbackManager.triggerCallback('popup-sanzone-refresh-selection');
		});

		$('popup-sanzone-save').on('click', function () {
			var $peText = $('[{$name}]'),
				$peHidden = $('[{$name}]__HIDDEN'),
				$peHiddenRaid = $('[{$name}]__HIDDEN2');

			if ($peText && $peHidden && $peHiddenRaid) {
				$peText.value = $selection_text.innerHTML;
				$peHidden.value = Object.toJSON(selected_devices);
				$peHiddenRaid.value = Object.toJSON(selected_raids);
			}

			popup_close();
		});

		idoit.callbackManager.registerCallback('popup-sanzone-refresh-selection', function () {
			var $devices = $tree.select('input[name="devicesInPool\[\]"]'),
				$raids = $tree.select('input[name="raidsInPool\[\]"]'),
				text = [];

			selected_devices = [];
			selected_raids = [];

			if ($devices.length) {
				$devices.each(function($el) {
					var val = $el.readAttribute('value');

					if ($el.checked) {
						text.push(devices[val]);
						selected_devices.push(val);
					}
				});
			}

			if ($raids.length) {
				$raids.each(function($el) {
					var val = $el.readAttribute('value');

					if ($el.checked) {
						text.push(raids[val]);
						selected_raids.push(val);
					}
				});
			}

			if (text.length === 0) {
				$selection_text.update('[{isys type="lang" ident="LC_UNIVERSAL__NONE_SELECTED"}]');
			} else {
				if (text.length === 1) {
					$('selection-text-label').update('[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]');
				} else {
					$('selection-text-label').update('[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECTS"}]');
				}

				$selection_text.update(text.join(', '));
			}
		});

		idoit.callbackManager.triggerCallback('popup-sanzone-refresh-selection');
	})();
</script>
