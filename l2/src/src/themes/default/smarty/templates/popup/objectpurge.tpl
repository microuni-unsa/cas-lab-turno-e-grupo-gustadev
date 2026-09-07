[{isys_group name="tom.popup.objectpurge"}]
<div id="popup-objectpurge">
    <div class="popup-header-ng">
        <h1>[{$headline}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content">
		<p class="m5">[{$message}]</p>
		<p class="m5">
			[{isys type="lang" ident="LC__CMDB__CATG__VIRTUAL_HOST_DISSOLVE_ACTIONTEXT_1"}]

            [{isys type="f_dialog" name="object_action"}]

			[{isys type="lang" ident="LC__CMDB__CATG__VIRTUAL_HOST_DISSOLVE_ACTIONTEXT_2"}]
		</p>

		<ul class="p0 m0 mb10 border-bottom border-top list-style-none">
			[{foreach $objects as $i => $title}]
				<li>
					<label class="p10 display-block mouse-pointer">
                        <input type="checkbox" checked="checked" class="objcheck" name="object[]" value="[{$i}]" /> <span>[{$title}]</span>
                    </label>
				</li>
			[{/foreach}]
		</ul>
		<button type="button" class="btn ml5 mr5" onclick="$$('.objcheck').invoke('setValue', 1);">
            <span>[{isys type="lang" ident="LC__UNIVERSAL__ALL_OBJECTS"}]</span>
        </button>
		<button type="button" class="btn" onclick="$$('.objcheck').invoke('setValue', 0);">
            <span>[{isys type="lang" ident="LC__UNIVERSAL__NO_OBJECT"}]</span>
        </button>
	</div>

	<div class="popup-footer-ng">
		<button id="popup-objpurge-accept" type="button" class="btn mr5">
			<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt=""/><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt=""/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<style>
    #popup-objectpurge li:hover {
        background-color: #e6e7e7;
    }
</style>

<script type="text/javascript">
	(function () {
		'use strict';

		var $popup = $('popup-objectpurge'),
			$accept_button = $('popup-objpurge-accept');

		$popup.select('.popup-closer').invoke('on', 'click', function () {
			popup_close();
		});

		$accept_button.on('click', function () {
			$('objects').setValue($$('.objcheck:checked').invoke('getValue').join(','));
			$('devirtualize_action').setValue($('object_action').getValue());

			popup_close();
		});

		if (!$('objects')) {
			$('isys_form').insert(new Element('input', {type: 'hidden', id: 'objects', name: 'objects'}));
		}

		if (!$('devirtualize_action')) {
			$('isys_form').insert(new Element('input', {type: 'hidden', id: 'devirtualize_action', name: 'devirtualize_action'}));
		}
	})();
</script>
[{/isys_group}]