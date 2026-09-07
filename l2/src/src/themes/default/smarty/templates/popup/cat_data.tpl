<style type="text/css">
	#data-list {
		overflow-y: auto;
	}

    #data-list label:hover {
        background-color: #e6e7e7;
    }
</style>

<div id="popup-cat-data">
    <div class="popup-header-ng">
        <h1>[{$browser_title}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="popup-content">
		<p class="p10">[{isys type="lang" ident="LC__POPUP__BROWSER__SELECTED_OBJECT"}]: <strong>[{$obj_title}]</strong></p>

		[{if is_array($data) && count($data) > 0}]
		<ul id="data-list" class="list-style-none border-top m0 p0" style="height:275px;">
			[{foreach $data as $key => $value}]
			<li>
				<label class="p10 display-block mouse-pointer">
					<input type="checkbox" name="item[]" value="[{$key}]" [{if is_array($preselection) && in_array($key, $preselection)}]checked="checked"[{/if}]/>
					<span class="ml5">[{$value}]</span>
				</label>
			</li>
			[{/foreach}]
		</ul>
		[{else}]
        <div class="m5 p5 text-blue display-flex align-items-center">
            <img src="[{$dir_images}]axialis/basic/button-info.svg" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__FILTER__NOTHING_FOUND_STD"}]</span>
        </div>
		[{/if}]
	</div>

	<div class="popup-footer-ng">
		<button type="button" class="btn mr5" id="popup-cat-data-accept">
			<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt=""/><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt=""/><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
		</button>
	</div>
</div>

<script language="JavaScript" type="text/javascript">
	(function () {
		var $popup = $('popup-cat-data'),
			$data_list = $('data-list'),
			$accept_button = $('popup-cat-data-accept');

		if ($data_list) {
			$accept_button.on('click', function () {
				var $view = $("[{$view_field}]"),
					$hidden = $("[{$hidden_field}]"),
					data_view = [],
					data_hidden = [];

				// Iterate through all checkboxes and get the values.
				$data_list.select('input[type=checkbox]').each(function ($checkbox) {
					if ($checkbox.checked) {
						data_hidden.push($checkbox.getValue());
						data_view.push($checkbox.next().textContent);
					}
				});

				// Write our new values to the fields.
				$hidden.setValue(Object.toJSON(data_hidden));
				$view.setValue(data_view.join(', '));

				popup_close();
			});
		}

		// Close the popup, when clicking ".popup-closer" elements.
		$popup.select('.popup-closer').invoke('on', 'click', function() {
			popup_close();
		});
	})();
</script>
